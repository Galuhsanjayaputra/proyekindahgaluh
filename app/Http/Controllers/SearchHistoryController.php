<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SearchHistory;
use Illuminate\Support\Facades\Auth;

class SearchHistoryController extends Controller
{
    /**
     * Tampilkan riwayat pencarian untuk pengguna yang sedang login.
     */
    public function index()
    {
        // Ambil riwayat pencarian berdasarkan user yang sedang login
        $searchHistories = SearchHistory::where('user_id', Auth::id())
            ->with('user') // Ambil relasi user (jika diperlukan di view)
            ->latest() // Urutkan dari yang terbaru
            ->get();

        // Kirim data ke view
        return view('search-history', compact('searchHistories'));
    }

    /**
     * Simpan riwayat pencarian pengguna.
     */
    public function store(Request $request)
    {
        // Validasi input pencarian
        $request->validate([
            'query' => 'required|string|max:255',
        ]);

        // Simpan data pencarian ke database
        SearchHistory::create([
            'user_id' => Auth::id(), // Pastikan user login
            'query' => $request->query, // Query pencarian
        ]);

        return response()->json(['message' => 'Riwayat pencarian berhasil disimpan']);
    }

    /**
     * Hapus riwayat pencarian tertentu.
     */
    public function delete(Request $request)
    {
        $history = SearchHistory::find($request->id);

        // Pastikan riwayat hanya dapat dihapus oleh pemiliknya
        if ($history && $history->user_id == Auth::id()) {
            $history->delete();
            return redirect()->route('search.history')->with('success', 'Riwayat pencarian berhasil dihapus.');
        }

        return redirect()->route('search.history')->with('error', 'Gagal menghapus riwayat pencarian.');
    }
}
