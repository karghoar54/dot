# Kargho DOT API

## API Versioning
All endpoints are now versioned. The current version is **v1** and all routes are prefixed with `/api/v1/`.

Example:
- `POST /api/v1/kargho/onboard-from-fmcsa/{dotnumber}`
- `GET /api/v1/dots`
- `GET /api/v1/dots/{dotnumber}`

When new versions are released, older versions will remain available under their own prefix (`/api/v2/`, etc.) to maintain compatibility with existing clients.

---

## Overview
This API provides endpoints for onboarding DOT numbers from FMCSA, listing and filtering DOTs, and retrieving detailed information about each DOT. It is designed to be the single source of business logic, validation, and messaging, enabling any frontend (web, mobile, etc.) to be as simple as possible. All internationalization, pagination, error handling, and filtering are managed by the backend.

---

## Features

### 1. Onboarding DOTs from FMCSA
- **Endpoint:** `POST /api/onboard-from-fmcsa/{dotnumber}`
- Triggers onboarding for a DOT number from FMCSA, executing a stored procedure in the KarghoUS SQL Server database.
- Receives the DOT number as a URL parameter, and password and country ID in the request body.
- Validates input, encrypts the password, and returns clear success or error messages.

### 2. List and Filter DOTs
- **Endpoint:** `GET /api/dots`
- Returns a paginated list of DOTs with filters for real columns (EIN, DOT, company name, city, state, etc.) and special filters (VIN, license) via related inspections.
- Pagination and filtering are fully handled by the backend.

### 3. DOT Details
- **Endpoint:** `GET /api/dots/{dotnumber}`
- Returns detailed information for a specific DOT, including basic info, details, and all related inspections.

---

## Internationalization (i18n)
- All API messages (success, error, validation) are localized using language files (`lang/en/messages.php`, `lang/es/messages.php`).
- The API responds in the language specified by the `Accept-Language` header.
- Documentation is in English, but responses can be in multiple languages.

---

## Error Handling & Validation
- All validation is performed in the backend, with clear, structured error messages.
- SQL Server errors are sanitized to avoid exposing internal details, showing only the relevant message.
- All errors and validation failures follow a consistent response format.

---

## Pagination & Filtering
- Pagination and filtering are managed by the backend, returning only the necessary data for the frontend.
- The maximum allowed value for `per_page` is 100; if a higher value is requested, only 100 results will be returned per page.
- Responses include all required fields for pagination and user messages.

---

## Data Exposure & Security
- Only necessary fields are exposed in API responses (e.g., passwords are hidden).
- Endpoints are documented with clear parameter and response definitions.

---

## Multi-Client Ready
- The API is designed to be consumed by any client (web, mobile, other systems) without requiring business logic or validation on the frontend.
- All messages, errors, and data structures are consistent and predictable.

---

## Example Response Formats

### Success
```json
{
  "success": true,
  "message": "Onboarding executed successfully."
}
```

### Validation Error
```json
{
  "success": false,
  "message": "Validation error.",
  "errors": {
    "password": ["The password field is required."]
  }
}
```

### SQL Error
```json
{
  "success": false,
  "message": "Error executing onboarding.",
  "error": "DOT Number do not exists."
}
```

---

## How to Use
1. Set the `Accept-Language` header to your preferred language (`en`, `es`, etc.).
2. Use the documented endpoints, sending only the required parameters.
3. Handle all messages, errors, and pagination as provided by the backend.

---

## Documentation
- Full API documentation is generated with Scribe and available in the `public/docs` directory or at `/docs` if hosted.
- The documentation includes all endpoints, parameters, and example responses.

---

## Contributing
- Please ensure all new endpoints follow the same conventions for validation, error handling, and internationalization.
- Update language files and documentation as needed.

---

## License
This project is proprietary and not open source.
