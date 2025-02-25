<?php

namespace App\Http\Controllers;

use App\Models\modelDetailTransaksi;
use App\Models\product;
use App\Models\tblCart;
use App\Models\transaksi;
use App\Models\User;
use App\Models\Ekspedisi;
use App\Models\pembayaran;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use RealRashid\SweetAlert\Facades\Alert;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;


    public function shop(Request $request)
    {
        if ($request->has('kategory') && $request->has('type')) {
            $category = $request->input('kategory');
            $type = $request->input('type');
            $data = product::where('kategory', $category)
                ->orWhere('type', $type)->paginate(5);
        } else {
            $data = product::paginate(5);
        }
        $countKeranjang = tblCart::where(['idUser' => 'guest123', 'status' => 0])->count();

        return view('pelanggan.page.shop', [
            'title'     => 'Shop',
            'data'      => $data,
            'count'     => $countKeranjang,
        ]);
    }
    public function transaksi()
    {
        $db = tblCart::with('product')->where(['idUser' => 'guest123', 'status' => 0])->get();
        $countKeranjang = tblCart::where(['idUser' => 'guest123', 'status' => 0])->count();



        return view('pelanggan.page.transaksi', [
            'title'     => 'keranjang',
            'count'     => $countKeranjang,
            'data'      => $db
        ]);
    }


    // Check out
    public function checkout()
    {
        $countKeranjang = tblCart::where(['idUser' => 'guest123', 'status' => 0])->count();
        $code = transaksi::count();
        $codeTransaksi = date('Ymd') . ($code + 1);
    
        // Hitung detail belanja
        $detailBelanja = modelDetailTransaksi::where(['id_transaksi' => $codeTransaksi, 'status' => 0])
            ->sum('price');
        $jumlahBarang = modelDetailTransaksi::where(['id_transaksi' => $codeTransaksi, 'status' => 0])
            ->count('id_barang');
        $qtyBarang = modelDetailTransaksi::where(['id_transaksi' => $codeTransaksi, 'status' => 0])
            ->sum('qty');
    
        // Hitung total PPN dan Ongkir
        $ppn = modelDetailTransaksi::where(['id_transaksi' => $codeTransaksi, 'status' => 0])
            ->sum('ppn');
        $ongkir = modelDetailTransaksi::where(['id_transaksi' => $codeTransaksi, 'status' => 0])
            ->sum('ongkir');
    
        // Ambil nama ekspedisi dan pembayaran
        $namaEkspedisi = Ekspedisi::pluck('namaEkspedisi');
        $namaPembayaran = pembayaran::pluck('namaPembayaran');
    
        return view('pelanggan.page.checkOut', [
            'title'         => 'Check Out',
            'count'         => $countKeranjang,
            'detailBelanja' => $detailBelanja,
            'jumlahbarang'  => $jumlahBarang,
            'qtyOrder'      => $qtyBarang,
            'ppn'           => $ppn,
            'ongkir'        => $ongkir,
            'codeTransaksi' => $codeTransaksi,
            'namaEkspedisi' => $namaEkspedisi,
            'namaPembayaran'=> $namaPembayaran,
        ]);
    }
    
    public function prosesCheckout(Request $request, $id)
    {
        $data = $request->all();
        $code = transaksi::count();
        $codeTransaksi = date('Ymd') . $code + 1;
        $product = Product::find($data['idBarang']);

        $detailTransaksi = new modelDetailTransaksi();
        $fieldDetail = [
            'id_transaksi' => $codeTransaksi,
            'id_barang'    => $data['idBarang'],
            'qty'          => $data['qty'],
            'ppn'          => $product->ppn,
            'ongkir'       => $product->ongkir,
            'price'        => $data['total']
        ];
        $detailTransaksi::create($fieldDetail);



        $fieldCart = [
            'qty'          => $data['qty'],
            'price'        => $data['total'],
            'status'       => 1,
        ];
        tblCart::where('id', $id)->update($fieldCart);

        Alert::toast('Checkout Berhasil', 'success');
        return redirect()->route('checkout');
    }

    public function prosesPembayaran(Request $request)
    {
        $data = $request->all();
        $dbTransaksi = new transaksi();
        $dbTransaksi->code_transaksi    = $data['code'];
        $dbTransaksi->total_qty         = $data['totalQty'];
        $dbTransaksi->total_harga       = $data['dibayarkan'];
        $dbTransaksi->nama_customer     = $data['namaPenerima'];
        $dbTransaksi->alamat            = $data['alamatPenerima'];
        $dbTransaksi->no_tlp            = $data['tlp'];

        $dbTransaksi->save();

        $dataCart = modelDetailTransaksi::where('id_transaksi', $data['code'])->get();
        foreach ($dataCart as $x) {
            $dataUp = modelDetailTransaksi::where('id', $x->id)->first();
            $dataUp->status    = 1;
            $dataUp->save();

            $idProduct = product::where('id', $x->id_barang)->first();
            $idProduct->quantity = $idProduct->quantity - $x->qty;
            $idProduct->quantity_out = $x->qty;
            $idProduct->save();
        }

        Alert::alert()->success('Transaksi berhasil', 'Ditunggu barangnya');
        return redirect()->route('home');
    }

    //




    public function ekspedisi()
    {
        $dataEkspedisi = Ekspedisi::select('*')->get();
        return view('admin.page.ekspedisi', [
            'name'      => "Ekspedisi",
            'title'     => 'Ekspedisi',
            'dataEkspedisi'  => $dataEkspedisi,
        ]);
    }


    public function addEkspedisi()
    {
        return view('admin.page.addEkspedisi', [
            'name'      => "Ekspedisi",
            'title'     => 'Ekspedisi',
        ]);
    }

    public function saveEkspedisi(Request $request)
    {
        $ekspedisi = new Ekspedisi();
        $ekspedisi->namaEkspedisi = $request->get('namaEkspedisi');
        $ekspedisi->save();
        return redirect()->route('ekspedisi');
    }

    public function editEkspedisi($id)
    {
        $cariEkspedisi = Ekspedisi::where('id', $id)->first();
        return view('admin.page.editEkspedisi', [
            'name'      => "Edit Ekspedisi",
            'title'     => 'Edit Ekspedisi',
            'cariEkspedisi'  => $cariEkspedisi,
        ]);
    }

    public function updateEkspedisi(Request $request, $id)
    {
        $updateEkspedisi = Ekspedisi::where('id', $id)->first();
        $updateEkspedisi->namaEkspedisi = $request->get('namaEkspedisi');
        $updateEkspedisi->update();
        return redirect()->route('ekspedisi');
    }

    public function destroyEkspedisi($id)
    {
        $deleteEkspedisi = Ekspedisi::find($id);
        $deleteEkspedisi->delete();
        return redirect()->route('ekspedisi');
    }

    // pembayaran
    public function pembayaran()
    {
        $dataPembayaran = pembayaran::select('*')->get();
        return view('admin.page.pembayaran', [
            'name'      => "Pembayaran",
            'title'     => 'Pembayaran',
            'dataPembayaran'  => $dataPembayaran,
        ]);
    }

    public function addPembayaran()
    {
        return view('admin.page.addPembayaran', [
            'name'      => "Pembayaran",
            'title'     => 'Pembayaran',
        ]);
    }

    public function savePembayaran(Request $request)
    {
        $pembayaran = new pembayaran();
        $pembayaran->namaPembayaran = $request->get('namaPembayaran');
        $pembayaran->save();
        return redirect()->route('pembayaran');
    }

    public function editPembayaran($id)
    {
        $cariPembayaran = pembayaran::where('id', $id)->first();
        return view('admin.page.editPembayaran', [
            'name'      => "Edit Pembayaran",
            'title'     => 'Edit Pembayaran',
            'cariPembayaran'  => $cariPembayaran,
        ]);
    }

    public function updatePembayaran(Request $request, $id)
    {
        $updatePembayaran = Pembayaran::where('id', $id)->first();
        $updatePembayaran->namaPembayaran = $request->get('namaPembayaran');
        $updatePembayaran->update();
        return redirect()->route('pembayaran');
    }

    public function destroyPembayaran($id)
    {
        $deletePembayaran = pembayaran::find($id);
        $deletePembayaran->delete();
        return redirect()->route('pembayaran');
    }










    public function admin()
    {
        $dataProduct = product::count();
        $dataStock = product::sum('quantity');
        $dataTransaksi = transaksi::count();
        $dataPenghasilan = transaksi::sum('total_harga');
        return view('admin.page.dashboard', [
            'name'          => "Dashboard",
            'title'         => 'Admin Dashboard',
            'totalProduct'  => $dataProduct,
            'sumStock'      => $dataStock,
            'dataTransaksi' => $dataTransaksi,
        ]);
    }


    public function login()
    {
        return view('admin.page.login', [
            'name'      => "Login",
            'title'     => 'Admin Login',
        ]);
    }
    public function loginProses(Request $request)
    {
        Session::flash('error', $request->email);
        $dataLogin = [
            'email' => $request->email,
            'password'  => $request->password,
        ];

        $user = new User;
        $proses = $user::where('email', $request->email)->first();

        if ($proses->role !== "admin") {
            Session::flash('error', 'Kamu bukan admin');
            return back();
        } else {
            if (Auth::attempt($dataLogin)) {
                Alert::toast('Kamu berhasil login', 'success');
                $request->session()->regenerate();
                return redirect()->intended('/admin/dashboard');
            } else {
                Alert::toast('Email dan Password salah', 'error');
                return back();
            }
        }
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        Alert::toast('Kamu berhasil Logout', 'success');
        return redirect('admin');
    }
}
