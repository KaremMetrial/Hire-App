# Missing Information Request Feature Implementation

## Overview
Enhance the existing basic information request feature to track specific information requests (license number, expiry date, ID photo, etc.) in a structured way.

## Tasks

### 1. Database Migration
- [ ] Create migration for `booking_information_requests` table
- [ ] Define fields: id, booking_id, requested_field, is_required, status, notes, created_at, updated_at

### 2. Model Creation
- [ ] Create `BookingInformationRequest` model
- [ ] Define relationships with Booking
- [ ] Add fillable fields and casts

### 3. Request Classes Update
- [ ] Update `RequestInfoRequest` to accept array of specific fields to request
- [ ] Update `SubmitBookingInfoRequest` to validate based on requested fields

### 4. Service Layer Updates
- [ ] Update `BookingService::requestBookingInfo()` to create structured requests
- [ ] Update `BookingService::submitBookingInfo()` to handle structured submissions
- [ ] Add methods to get pending information requests for a booking

### 5. Controller Updates
- [ ] Update Vendor BookingController `requestInfo` method
- [ ] Update User BookingController `submitInfo` method
- [ ] Add method to get information requests for a booking

### 6. API Resources
- [ ] Create `BookingInformationRequestResource` for API responses

### 7. Routes Update
- [ ] Add route to get information requests for a booking (if needed)

### 8. Testing & Migration
- [ ] Run migration
- [ ] Test vendor request info endpoint
- [ ] Test user submit info endpoint
- [ ] Verify status changes work correctly

## Files to Create/Modify
- database/migrations/YYYY_MM_DD_HHMMSS_create_booking_information_requests_table.php
- app/Models/BookingInformationRequest.php
- app/Http/Requests/Vendor/RequestInfoRequest.php
- app/Http/Requests/User/SubmitBookingInfoRequest.php
- app/Services/BookingService.php
- app/Http/Controllers/Api/Vendor/BookingController.php
- app/Http/Controllers/Api/User/BookingController.php
- app/Http/Resources/BookingInformationRequestResource.php
- routes/api.php (if needed)
- routes/vendor.php (if needed)
