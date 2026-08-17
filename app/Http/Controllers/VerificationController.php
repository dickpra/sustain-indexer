<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyDocumentEmail;

class VerificationController extends Controller
{
    public function verifyEmail($token)
    {
        $document = Document::where('verification_token', $token)->first();

        if (!$document) return "Verification link is invalid or has expired.";

        $document->update(['is_verified' => true, 'verification_token' => null]);

        return "Congratulations! Your document has been successfully verified and now it is available in the search system.";
    }

    public function resendEmail(Request $request)
    {
        $request->validate(['document_number' => 'required|string']);
        
        $document = Document::where('document_number', $request->document_number)->first();

        if (!$document) {
            return response()->json(['error' => 'Document not found.'], 404);
        }

        if ($document->is_verified) {
            return response()->json(['error' => 'This document has already been verified and is available in the search system.'], 400);
        }

        dispatch(function () use ($document) {
            \Illuminate\Support\Facades\Mail::to($document->submitter_email)
                ->send(new \App\Mail\VerifyDocumentEmail($document));
        })->afterResponse();

        return response()->json(['message' => 'Verification email has been resent successfully! Please check your inbox or spam folder.']);
    }
}