<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\Institution;
use App\Models\Author;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class DocumentEditController extends Controller
{
    public function requestEdit(Request $request, $id)
    {
        $request->validate(['email' => 'required|email']);
        
        $document = Document::where('document_number', $id)->firstOrFail();

        if (strtolower($document->submitter_email) !== strtolower($request->email)) {
            return response()->json(['error' => 'Authentication failed: Email does not match the original submitter of this document.'], 403);
        }

        // 🔥 TRIK DEWA: Sisipkan timestamp updated_at ke dalam link
        $secureEditUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'document.edit', 
            now()->addHours(24), 
            [
                'id' => $document->document_number,
                'v' => $document->updated_at->timestamp // Kunci gembok sekali pakai
            ]
        );

        \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($document, $secureEditUrl) {
            $message->to($document->submitter_email)
                    ->subject('SustaIndex - Secure Document Edit Access')
                    ->html("
                        <h3>Secure Edit Request</h3>
                        <p>Hello,</p>
                        <p>You have requested to edit the metadata for the document: <br><b>{$document->title}</b></p>
                        <p>Please click the secure button below to access the edit form. For security reasons, this link is for <b>ONE-TIME USE ONLY</b> and will automatically expire once you save your changes (or within 24 hours).</p>
                        <br>
                        <a href='{$secureEditUrl}' style='background-color: #003366; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Open Edit Form</a>
                        <br><br>
                        <p style='color: gray; font-size: 12px;'>If you did not request this, please ignore this email.</p>
                    ");
        });

        return response()->json(['success' => true]);
    }

    // =======================================================
    // 11. FITUR EDIT: Tampilkan Form Edit (One-Time Link Check)
    // =======================================================
    public function editForm(Request $request, $id)
    {
        $document = Document::with('authors')->where('document_number', $id)->firstOrFail();
        
        // 🔥 PENJAGA PINTU: Cek apakah timestamp di link masih sama dengan di Database?
        // Jika dokumen sudah pernah di-save/di-update, otomatis timestamp-nya berbeda!
        if ($request->query('v') != $document->updated_at->timestamp) {
            return abort(403, '🔒 SECURITY ALERT: This edit link has already been used and is now expired. If you need to make further corrections, please request a new secure link from the document page.');
        }

        return view('edit_document', compact('document'));
    }

    // =======================================================
    // 12. FITUR EDIT: Proses Update Super Lengkap
    // =======================================================
    public function updateDocument(Request $request, $id)
    {
        $document = Document::with('authors')->where('document_number', $id)->firstOrFail();

        $request->validate([
            'title' => 'required|string',
            'abstract' => 'required|string',
            'sdgs' => 'required|array|min:1',
            'document_type' => 'required|string',
            'authors' => 'required|array|min:1',
            'authors.*.name' => 'required|string',
            'authors.*.email' => 'required|email',
            'authors.*.institution' => 'required|string',
            'journal_title' => 'required|string', // <-- Sekarang Wajib
            'publisher' => 'required|string',     // <-- Sekarang Wajib
        ]);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // ==========================================
            // 🔥 MAGIC PENGGABUNGAN SDGs (ANTI DUPLIKAT)
            // ==========================================
            $rawKeywords = $request->input('keywords', '');
            
            // 1. Pecah teks keyword dari form menjadi array (dipisah koma)
            $keywordArray = array_map('trim', explode(',', $rawKeywords));
            
            // 2. FILTER: Buang teks SDG lama yang ikut ter-load di text input Keywords
            $cleanKeywords = array_filter($keywordArray, function($kw) {
                // Hapus string yang depannya "SDG 1:", "SDG 12:", dll
                return !preg_match('/^SDG \d+:/i', $kw); 
            });
            
            // 3. Ambil SDG baru yang barusan dicentang di form
            $newSdgs = $request->input('sdgs', []);
            
            // 4. Gabungkan Keyword bersih dengan SDG yang baru
            $finalKeywordsArray = array_merge($cleanKeywords, $newSdgs);
            
            // 5. Jadikan 1 kalimat utuh lagi pakai koma (buang elemen yang kosong)
            $finalKeywords = implode(', ', array_filter($finalKeywordsArray));


            // 1. Update Tabel Utama Dokumen
            $document->update([
                'title' => $request->title,
                'journal_title' => $request->journal_title,
                'publisher' => $request->publisher,
                'abstract' => $request->abstract,
                'keywords' => $finalKeywords, // 🔥 GUNAKAN KEYWORD HASIL GABUNGAN DI SINI
                'document_type' => $request->document_type,
                'pub_year' => $request->pub_year,
                'doi' => $request->doi,
                'pages' => $request->pages,
                'reference_count' => $request->reference_count,
            ]);

            // 2. Sinkronisasi Data Author & Institusi
            $authorIdsToSync = [];

            foreach ($request->authors as $authorData) {
                // Cek atau Buat Institusi Baru
                $institution = \App\Models\Institution::firstOrCreate(
                    ['name' => $authorData['institution']],
                    [
                        'country' => $authorData['country'] ?? null,
                        'latitude' => $authorData['lat'] ?? null,
                        'longitude' => $authorData['lng'] ?? null
                    ]
                );

                // Update koordinat kampus jika ada perubahan dari Leaflet Map
                if (isset($authorData['lat']) && isset($authorData['lng'])) {
                    $institution->update([
                        'latitude' => $authorData['lat'],
                        'longitude' => $authorData['lng']
                    ]);
                }

                // Cari Author berdasarkan email (untuk menghindari duplikasi)
                $author = \App\Models\Author::firstOrCreate(
                    ['email' => $authorData['email']], 
                    [
                        'name' => $authorData['name'],
                        'country' => $authorData['country'] ?? null,
                        'institution_id' => $institution->id
                    ]
                );

                // Update jika ada perubahan typo pada nama/negara
                $author->update([
                    'name' => $authorData['name'],
                    'country' => $authorData['country'] ?? null,
                    'institution_id' => $institution->id
                ]);

                $authorIdsToSync[] = $author->id;
            }

            // Sync Pivot Table: Ini otomatis menghapus author yang di-remove, dan memasukkan yang di-add
            $document->authors()->sync($authorIdsToSync);

            \Illuminate\Support\Facades\DB::commit();

            // 3. Kirim Email Notifikasi
            \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($document) {
                $message->to($document->submitter_email)
                        ->subject('SustaIndex - Document Updated Successfully')
                        ->html("
                            <h3>Update Successful</h3>
                            <p>Hello,</p>
                            <p>The metadata for your document <b>{$document->title}</b> has been successfully updated in our index.</p>
                            <p>Thank you for keeping your academic records accurate!</p>
                        ");
            });

            return redirect('/document/' . $document->document_number)->with('success', 'Document information has been successfully updated!');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }
}