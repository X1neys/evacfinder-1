$(function(){
    const form = document.querySelector('.registration-form, .registration-lgu-form');
    
    const accountTypeSelect = document.getElementById('accountType');
    const btnRegister = document.getElementById('btn-register');
    const btnNext = document.getElementById('btn-next');
    
    function getErrorContainer(formElement) {
        if (!formElement) return null;
        return formElement.querySelector('.alert.alert-danger');
    }

    function hideError(formElement) {
        const errorContainer = getErrorContainer(formElement);
        if (errorContainer) {
            errorContainer.style.display = 'none';
            errorContainer.textContent = '';
        }
    }

    function showError(formElement, message) {
        const errorContainer = getErrorContainer(formElement);
        if (errorContainer) {
            errorContainer.style.display = 'block';
            errorContainer.textContent = message;
        }
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            const isLguForm = this.classList.contains('registration-lgu-form');
            const requiredFields = isLguForm
                ? ['lguOfficeName', 'lguOfficeEmail', 'lguOfficeNumber', 'lguContactNumber', 'lguOfficeType', 'lguDepartment', 'lguRegion', 'lguProvince', 'lguPosition']
                : ['firstName', 'lastName', 'dateOfBirth', 'sex', 'emailAddress', 'phoneNumber', 'region', 'password', 'confirmPassword'];

            let isValid = true;
            let firstInvalid = null;

            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    if (!field.value.trim()) {
                        field.classList.add('is-invalid');
                        isValid = false;
                        if (!firstInvalid) {
                            firstInvalid = field;
                        }
                    } else {
                        field.classList.remove('is-invalid');
                    }
                }
            });

            if (!isLguForm) {
                const password = document.getElementById('password');
                const confirmPassword = document.getElementById('confirmPassword');
                if (password && confirmPassword && password.value !== confirmPassword.value) {
                    isValid = false;
                    showError(this, 'Password and Confirm Password must match.');
                    confirmPassword.classList.add('is-invalid');
                    if (!firstInvalid) {
                        firstInvalid = confirmPassword;
                    }
                }
            }

            if (!isValid) {
                e.preventDefault();
                const errorContainer = getErrorContainer(this);
                if (errorContainer && !errorContainer.textContent.trim()) {
                    showError(this, 'Please fill in all required fields.');
                }
                if (firstInvalid) {
                    firstInvalid.focus();
                }
                return false;
            }

            hideError(this);
        });
    }
    
    // Back button handler
    $("#btn-back").click(function (e) {
        e.preventDefault();
        
        // Check URL to determine which page we're on
        if (window.location.href.indexOf('registration_lgu') > -1) {
            // LGU page - go back to user registration
            if (confirm('Are you sure you want to go back? Your LGU details will be lost, but your personal info will be preserved.')) {
                window.location.href = '?route=registration';
            }
        } else {
            // User registration page - go back to login
            if (confirm('Are you sure you want to leave the page?')) {
                const form = document.querySelector('.registration-form');
                if (form) form.reset();
                
                document.querySelectorAll('.is-invalid').forEach(field => {
                    field.classList.remove('is-invalid');
                });
                
                hideError();
                window.location.href = '?route=login';
            }
        }
    });

    // Initial setup
    if (accountTypeSelect) {
        if (accountTypeSelect.value === 'lgu') {
            btnRegister.hidden = true;
            btnNext.hidden = false;
        } else {
            btnRegister.hidden = false;
            btnNext.hidden = true;
        }
        
        accountTypeSelect.addEventListener('change', function () {
            if (this.value === 'lgu') {
                btnRegister.hidden = true;
                btnNext.hidden = false;
            } else {
                btnRegister.hidden = false;
                btnNext.hidden = true;
            }
        });
    }

    $(".togglePassword").click(function(e){
        e.preventDefault(); // prevents accidental form submit
        const targetId = $(this).data('target');
        const input = $("#" + targetId);
        
        if(input.attr("type") === "password"){
            input.attr("type", "text");
            $(this).removeClass("fa-eye-slash").addClass("fa-eye");
        } else {
            input.attr("type", "password");
            $(this).removeClass("fa-eye").addClass("fa-eye-slash");
        }
    });

});