# Kargho DOT API Documentation

## API Versioning
All endpoints are versioned. The current version is **v1** and all routes are prefixed with `/api/v1/`.

Example:
- `POST /api/v1/kargho/onboard-from-fmcsa/{dotnumber}`
- `DELETE /api/v1/kargho/offboard-from-fmcsa/{dotnumber}`
- `GET /api/v1/dots`
- `GET /api/v1/dots/{dotnumber}`

When new versions are released, older versions will remain available under their own prefix (`/api/v2/`, etc.) to maintain compatibility with existing clients.

---

## Overview
This API provides endpoints for onboarding and offboarding DOT numbers from FMCSA, listing and filtering DOTs, and retrieving detailed information about each DOT. It is designed to be the single source of business logic, validation, and messaging, enabling any frontend (web, mobile, etc.) to be as simple as possible. All internationalization, pagination, error handling, and filtering are managed by the backend.

---

## Features
- Onboarding DOTs from FMCSA
- Offboarding DOTs from KarghoUS (DELETE)
- List and filter DOTs
- Retrieve DOT details
- Internationalization (i18n)
- Centralized error handling and validation
- Backend-managed pagination and filtering
- Secure and minimal data exposure
- Multi-client ready

---

For full details, see the README in the repository.
