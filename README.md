# 📚 SustainDex - Academic Indexing System

SustainDex is a lightweight, high-performance academic indexing system built with Laravel 10. Inspired by global research repositories like ERIC and Google Scholar, it allows authors to submit their research documents for indexing utilizing a highly secure, zero-storage-footprint architecture.

## ✨ Key Features

* **🛡️ Smart Dual Validation (Anti-Spam):** Automatically parses and scans uploaded PDF files to ensure the Title and Author names perfectly match the submission form data before accepting the document.
* **🗑️ Zero-Storage Footprint:** Extracts necessary metadata—including automatic DOI (Digital Object Identifier) detection via Regex—and immediately deletes the physical PDF file from the server. This keeps the hosting environment incredibly lightweight.
* **📧 Secure Email Verification:** Implements a strict publication workflow. Submissions remain "Pending" in the database until the author clicks a secure, encrypted verification link sent to their email.
* **🔍 Faceted Search Engine:** A fast, Google-like search interface equipped with dynamic sidebar filters (by Document Type and Publication Year) to help users discover relevant academic materials easily.
* **🎓 Academic UI/UX:** A clean, professional, and fully responsive user interface utilizing Bootstrap 5 and Vanilla JavaScript for a seamless Single Page Application (SPA)-like experience.

## 🛠️ Tech Stack

* **Backend:** Laravel 10 (PHP)
* **Database:** MySQL / PostgreSQL
* **Frontend:** Blade Templating, Bootstrap 5, Vanilla JS
* **PDF Parser:** `smalot/pdfparser` (Pure PHP library)

## 🚀 Installation & Setup

Follow these steps to set up SustainDex on your local development environment.

### Prerequisites
* PHP >= 8.1
* Composer
* MySQL or similar relational database
* Node.js & NPM (Optional, for frontend asset bundling)

### Step 1: Clone the Repository
```bash
git clone [https://github.com/dickpra/sustain-indexer.git](https://github.com/dickpra/sustain-indexer.git)
cd sustaindex