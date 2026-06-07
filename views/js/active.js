// ── VIEW HISTORY ─────────────────────────────────────────────────────────────
$(document).on('click', '.view-history', function () {
    const centerId   = $(this).data('center-id');
    const centerName = $(this).data('center-name');

    $('#history_center_name').text(centerName);
    $('#history-tbody').html('<td><td colspan="5" class="text-center">Loading...</td></tr>');
    $('#historyModal').modal('show');

    $.ajax({
        url:      'ajax/get_history.ajax.php',
        method:   'POST',
        data:     { center_id: centerId },
        dataType: 'json',
        success: function (response) {
            if (response.success && response.data.length > 0) {
                let rows = '';
                $.each(response.data, function (i, row) {
                    let changesList = '';
                    if (row.changes && row.changes.length > 0) {
                        changesList = '<ul class="mb-0 ps-3">';
                        $.each(row.changes, function (j, change) {
                            changesList += `<li>${change}</li>`;
                        });
                        changesList += '</ul>';
                    }

                    rows += `<tr>
                        <td>${row.history_date}</td>
                        <td><span class="badge ${
                            row.action_made === 'Created' ? 'bg-success' :
                            row.action_made === 'Updated' ? 'bg-primary' :
                            row.action_made === 'Occupancy Updated' ? 'bg-warning' :
                            'bg-secondary'
                        }">${row.action_made}</span></td>
                        <td>${changesList}</td>
                        <td>${row.remarks || '-'}</td>
                        <td>${row.changed_by_name}</td>
                    </tr>`;
                });
                $('#history-tbody').html(rows);
            } else {
                $('#history-tbody').html('<tr><td colspan="5" class="text-center">No history records found.</td></tr>');
            }
        },
        error: function () {
            $('#history-tbody').html('<tr><td colspan="5" class="text-center text-danger">Failed to load history.</td></tr>');
        }
    });
});

$('#viewSchedulesModal .btn-secondary').on('click', function() {
    $('#viewSchedulesModal').modal('hide');
});

$('#cancelEditCenter').on('click', function() {
    $('#editCenterModal').modal('hide');
});

// Edit Center functionality - Add this to your $(document).ready(function() { ... });
$(document).on('click', '.edit-center-dropdown', function(e) {
    e.stopPropagation();
    
    // Get all data attributes from the button
    var centerId = $(this).data('center-id');
    var centerName = $(this).data('center-name');
    var category = $(this).data('category');
    var status = $(this).data('status');
    var barangay = $(this).data('barangay');
    var city = $(this).data('city');
    var province = $(this).data('province');
    var address = $(this).data('address');
    var capacity = $(this).data('capacity');
    var currentOccupants = $(this).data('current-occupants');
    var contactNumber = $(this).data('contact-number');
    var contactPerson = $(this).data('contact-person');
    var latitude = $(this).data('latitude');
    var longitude = $(this).data('longitude');
    var estimatedCapacity = $(this).data('estimated-capacity');
    var accessibility = $(this).data('accessibility');
    var availableFacilities = $(this).data('available-facilities');
    var remarks = $(this).data('remarks');
    
    // Populate the edit modal fields (you need to create this modal first)
    $('#edit_center_id').val(centerId);
    $('#edit_center_name').val(centerName);
    $('#edit_category').val(category);
    $('#edit_status').val(status);
    $('#edit_barangay').val(barangay);
    $('#edit_city').val(city);
    $('#edit_province').val(province);
    $('#edit_address').val(address);
    $('#edit_capacity').val(capacity);
    $('#edit_current_occupants').val(currentOccupants);
    $('#edit_contact_number').val(contactNumber);
    $('#edit_contact_person').val(contactPerson);
    $('#edit_latitude').val(latitude);
    $('#edit_longitude').val(longitude);
    $('#edit_estimated_capacity').val(estimatedCapacity);
    $('#edit_accessibility').val(accessibility);
    $('#edit_available_facilities').val(availableFacilities);
    $('#edit_remarks').val(remarks);
    
    // Show the edit modal
    $('#editCenterModal').modal('show');
});

// Save edited center
$('#confirmEditCenter').on('click', function() {
    var formData = new FormData($('#editCenterForm')[0]);
    
    Swal.fire({
        title: 'Save Changes?',
        text: 'Are you sure you want to update this center?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, save changes'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ title: 'Updating...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            
            $.ajax({
                url: "ajax/update_center.ajax.php",
                method: "POST",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire('Error', 'Failed to update center', 'error');
                }
            });
        }
    });
});

// Edit Center - Populate modal with facility info
$(document).on('click', '.edit-center-dropdown', function(e) {
    e.stopPropagation();
    
    // Get all data attributes from the button
    var centerId = $(this).data('center-id');
    var centerName = $(this).data('center-name');
    var category = $(this).data('category');
    var status = $(this).data('status');
    var barangay = $(this).data('barangay');
    var city = $(this).data('city');
    var province = $(this).data('province');
    var address = $(this).data('address');
    var capacity = $(this).data('capacity');
    var currentOccupants = $(this).data('current-occupants');
    var contactNumber = $(this).data('contact-number');
    var contactPerson = $(this).data('contact-person');
    var latitude = $(this).data('latitude');
    var longitude = $(this).data('longitude');
    var estimatedCapacity = $(this).data('estimated-capacity');
    var accessibility = $(this).data('accessibility');
    var availableFacilities = $(this).data('available-facilities');
    var remarks = $(this).data('remarks');
    
    // Populate basic info
    $('#edit_center_id').val(centerId);
    $('#edit_center_name').val(centerName);
    $('#edit_category').val(category);
    $('#edit_status').val(status);
    $('#edit_barangay').val(barangay);
    $('#edit_city').val(city);
    $('#edit_province').val(province);
    $('#edit_address').val(address);
    
    // Populate capacity info
    $('#edit_capacity').val(capacity);
    $('#edit_current_occupants').val(currentOccupants);
    $('#edit_estimated_capacity').val(estimatedCapacity);
    
    // Populate contact info
    $('#edit_contact_number').val(contactNumber);
    $('#edit_contact_person').val(contactPerson);
    $('#edit_latitude').val(latitude);
    $('#edit_longitude').val(longitude);
    
    // Populate facility info
    $('#edit_accessibility').val(accessibility);
    $('#edit_available_facilities').val(availableFacilities);
    $('#edit_remarks').val(remarks);
    
    // Load additional facility data from the center's full info
    var detailsRow = $('.details-row-' + centerId);
    
    // Get facility info from the expanded row if available
    if (detailsRow.length) {
        var waterSupply = detailsRow.find('td:contains("Water Supply:")').next().text().trim();
        var electricity = detailsRow.find('td:contains("Electricity:")').next().text().trim();
        var numRooms = detailsRow.find('td:contains("Rooms:")').next().text().trim();
        var restroomsCount = detailsRow.find('td:contains("Restrooms:")').next().text().trim();
        var hasWifi = detailsRow.find('td:contains("WiFi:")').html().includes('fa-check');
        var hasCanteen = detailsRow.find('td:contains("Canteen:")').html().includes('fa-check');
        var hasMedical = detailsRow.find('td:contains("Medical Station:")').html().includes('fa-check');
        
        $('#edit_water_supply').val(waterSupply !== 'N/A' ? waterSupply : '');
        $('#edit_electricity').val(electricity !== 'N/A' ? electricity : '');
        $('#edit_num_rooms').val(numRooms !== 'N/A' ? numRooms : '');
        $('#edit_restrooms_count').val(restroomsCount !== 'N/A' ? restroomsCount : '');
        $('#edit_has_wifi').prop('checked', hasWifi);
        $('#edit_has_canteen').prop('checked', hasCanteen);
        $('#edit_has_medical').prop('checked', hasMedical);
    } else {
        // If details not loaded, fetch via AJAX
        $.ajax({
            url: "ajax/get_center_details.ajax.php",
            method: "POST",
            data: { center_id: centerId },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    $('#edit_water_supply').val(response.water_supply || '');
                    $('#edit_electricity').val(response.electricity || '');
                    $('#edit_num_rooms').val(response.num_rooms || '');
                    $('#edit_restrooms_count').val(response.restrooms_count || '');
                    $('#edit_has_wifi').prop('checked', response.has_wifi == 1);
                    $('#edit_has_canteen').prop('checked', response.has_canteen == 1);
                    $('#edit_has_medical').prop('checked', response.has_medical == 1);
                }
            }
        });
    }
    
    // Show the modal
    $('#editCenterModal').modal('show');
});

// Save edited center
$('#confirmEditCenter').on('click', function() {
    var formData = new FormData($('#editCenterForm')[0]);
    
    // Add checkbox values (since unchecked checkboxes don't send values)
    formData.append('has_wifi', $('#edit_has_wifi').is(':checked') ? 1 : 0);
    formData.append('has_canteen', $('#edit_has_canteen').is(':checked') ? 1 : 0);
    formData.append('has_medical', $('#edit_has_medical').is(':checked') ? 1 : 0);
    
    Swal.fire({
        title: 'Save Changes?',
        text: 'Are you sure you want to update this center?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, save changes'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ 
                title: 'Updating...', 
                allowOutsideClick: false, 
                didOpen: () => Swal.showLoading() 
            });
            
            $.ajax({
                url: "ajax/update_center.ajax.php",
                method: "POST",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    Swal.close();
                    console.error('AJAX Error:', error);
                    Swal.fire('Error', 'Failed to update center. Please try again.', 'error');
                }
            });
        }
    });
});