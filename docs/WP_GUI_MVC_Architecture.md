# WP GUI & MVC Integration Architecture

## Overview
This document describes the architecture, integration points, and design patterns for the WordPress (WP) GUI module in the PHPABC Canntaireachd project. It also formalizes the Model-View-Controller (MVC) separation for both the WP GUI and CLI entry points.

---

## 1. WP GUI Module

### 1.1 Purpose
- Provides a user-friendly web interface for uploading, processing, and downloading ABC/canntaireachd files.
- Integrates with the core parser and processing pipeline.
- Supports user authentication, file management, and result visualization.

### 1.2 Integration Points
- **File Upload:** Users upload ABC files via the WP admin interface. Files are validated and stored securely.
- **Processing Trigger:** Uploaded files are passed to the core parser (via service/controller) for processing.
- **Result Display:** Processed results (ABC, canntaireachd, errors, diffs) are displayed in the GUI and available for download.
- **Error Handling:** Parsing and processing errors are surfaced to the user with actionable messages.
- **User Management:** Leverages WP authentication and permissions for access control.

### 1.3 Extensibility
- The GUI is modular, allowing for new features (e.g., batch processing, result history, advanced options) without impacting the core parser.
- Hooks and filters are provided for WP plugin extensibility.

---

## 2. MVC Separation

### 2.1 Model
- **Domain Models:** `AbcTune`, `AbcHeader`, `AbcVoice`, `AbcBar`, `AbcNote`, `AbcLyrics`, etc.
- **Processing Pipeline:** Classes responsible for parsing, validation, and canntaireachd generation.

### 2.2 View
- **WP GUI:** WordPress admin pages, forms, and result displays (HTML, CSS, JS).
- **CLI Output:** Console output, file writes, and error logs.

### 2.3 Controller
- **WP Controller:** Handles HTTP requests, file uploads, invokes processing pipeline, and prepares data for the view.
- **CLI Entrypoint:** Parses CLI arguments, invokes processing pipeline, and manages output.

### 2.4 Shared Services
- **File Storage:** Abstracted file storage for uploads and results (WP media library, local disk).
- **Error Reporting:** Unified error and exception handling for both GUI and CLI.
- **Security:** Input validation, sanitization, and permission checks.

---

## 3. Acceptance Criteria
- WP GUI must allow upload, processing, and download of ABC/canntaireachd files.
- All processing logic must be shared between CLI and GUI (no duplication).
- MVC boundaries must be respected: no business logic in views, no direct output in models.
- Errors must be user-friendly and actionable in the GUI.
- WP GUI must be extensible via hooks/filters.

---

## 4. References
- [ABC Parser Refactor Architecture](./PM/Architecture.md)
- [Project Requirements](./BA/Requirements.md)
- [QA Plan](./QA/QA.md)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
