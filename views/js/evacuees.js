$(function () {
    var today = new Date().toISOString().split('T')[0];
    $("#registration_date").val(today);
    $("#arrival_date").val(today);

    if ($.fn.select2) {
        $("#evacuation_center_id").select2({
            placeholder: "- select evacuation center -",
            allowClear: true,
            width: '100%'
        });
    }

    $("#birth_date").on("change", function() {
        var birthDate = new Date($(this).val());
        var today = new Date();
        var age = today.getFullYear() - birthDate.getFullYear();
        var m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        if (age > 0 && age < 120) {
            $("#age").val(age);
        }
    });
    
    $("#btn-new").click(function() {
        Swal.fire({
            title: 'Create New Evacuee Registration?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-label-secondary'
            },
            buttonsStyling: false
        }).then(function(result) {
            if (result.value) {
                window.location = 'evacuees';
            }
        });
    });
    
    $("#btn-save").click(function() {
        var requiredFields = [
            { id: "#last_name", label: "Last Name" },
            { id: "#first_name", label: "First Name" },
            { id: "#registration_date", label: "Registration Date" },
            { id: "#evacuation_center_id", label: "Evacuation Center Assigned" }
        ];
        
        var emptyFields = [];
        requiredFields.forEach(function(field) {
            var value = $(field.id).val();
            if (!value || value.trim() === '') {
                emptyFields.push(field.label);
            }
        });
        
        if (emptyFields.length > 0) {
            Swal.fire({
                title: 'Required Fields Missing',
                icon: 'warning',
                html: '<div style="text-align:left;margin-left:20px;">' +
                      '<p>The following fields are required:</p>' +
                      '<ul>' +
                      emptyFields.map(f => `<li>${f}</li>`).join('') +
                      '</ul></div>',
                confirmButtonText: 'OK',
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
            });
            return;
        }
        
        Swal.fire({
            title: 'Save Evacuee Registration?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-label-secondary'
            },
            buttonsStyling: false
        }).then(function(result) {
            if (result.value) {
                saveEvacuee();
            }
        });
    });
    
    function saveEvacuee() {
        var formData = new FormData();
        formData.append("trans_type", $("#trans_type").val());
        formData.append("encodedby", $("#encodedby").val());
        formData.append("registration_date", $("#registration_date").val());
        formData.append("last_name", $("#last_name").val());
        formData.append("first_name", $("#first_name").val());
        formData.append("middle_name", $("#middle_name").val());
        formData.append("extension_name", $("#extension_name").val());
        formData.append("relation_to_head", $("#relation_to_head").val());
        formData.append("sex", $("#sex").val());
        formData.append("birth_date", $("#birth_date").val());
        formData.append("age", $("#age").val());
        formData.append("civil_status", $("#civil_status").val());
        formData.append("occupation", $("#occupation").val());
        formData.append("contact_number", $("#contact_number").val());
        formData.append("complete_address", $("#complete_address").val());
        formData.append("emergency_contact_person", $("#emergency_contact_person").val());
        formData.append("emergency_contact_number", $("#emergency_contact_number").val());
        formData.append("condition_pregnant", $("#condition_pregnant").is(":checked") ? 1 : 0);
        formData.append("condition_lactating", $("#condition_lactating").is(":checked") ? 1 : 0);
        formData.append("condition_elderly", $("#condition_elderly").is(":checked") ? 1 : 0);
        formData.append("condition_pwd", $("#condition_pwd").is(":checked") ? 1 : 0);
        formData.append("condition_4ps", $("#condition_4ps").is(":checked") ? 1 : 0);
        formData.append("pwd_type", $("#pwd_type").val());
        formData.append("health_status", $("#health_status").val());
        formData.append("emergency_medical_condition", $("#emergency_medical_condition").val());
        formData.append("medications_taken", $("#medications_taken").val());
        formData.append("known_allergies", $("#known_allergies").val());
        formData.append("evacuation_center_id", $("#evacuation_center_id").val());
        formData.append("arrival_date", $("#arrival_date").val());
        formData.append("departure_date", $("#departure_date").val());
        formData.append("evacuee_status", $("#evacuee_status").val());
        
        $.ajax({
            url: "ajax/evacuees_save.ajax.php",
            method: "POST",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "text",
            success: function(response) {
                if (response != 'error') {
                    Swal.fire({
                        title: 'Evacuee Successfully Registered!',
                        icon: 'success',
                        confirmButtonText: 'Got it',
                        customClass: { confirmButton: 'btn btn-success' },
                        buttonsStyling: false
                    }).then(function(result) {
                        if (result.value) {
                            window.location = '?route=home';
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to save evacuee registration.',
                        icon: 'error',
                        confirmButtonText: 'Got it',
                        customClass: { confirmButton: 'btn btn-danger' },
                        buttonsStyling: false
                    });
                }
            },
            error: function() {
                Swal.fire({
                    title: 'Oops. Something went wrong!',
                    icon: 'error',
                    confirmButtonText: 'Got it',
                    customClass: { confirmButton: 'btn btn-danger' },
                    buttonsStyling: false
                });
            }
        });
    }
    
    $("#btn-back").click(function() {
        window.location = '?route=home';
    });

    $("#btn-print").click(function(){
        window.open("reports/evacuee_form.php", "_blank");
    });

});