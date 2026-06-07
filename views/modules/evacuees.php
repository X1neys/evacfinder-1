<div class="container-fluid flex-grow-1 container-p-y">
    <form class="evacuee-form" method="POST" autocomplete="nope">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">

                    <div class="card-header sticky-element bg-label-secondary d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
                        <h5 class="card-title mb-sm-0 me-2">EVACUEE REGISTRATION</h5>
                        <input type="hidden" name="encodedby" id="encodedby" value="<?php echo $_SESSION['userid']; ?>">
                        <input type="hidden" name="trans_type" id="trans_type" value="New">
                        <input type="hidden" name="evacuee_id" id="evacuee_id" value="">
                    </div>

                    <div class="card-body pt-12">
                        <div class="row">
                            <div class="col-lg-12">

                                <!-- Row 1: Registration Date -->
                                <div class="row g-6">
                                    <div class="col-md-4">
                                        <label class="form-label" for="registration_date">Registration Date :</label>
                                        <input type="date" id="registration_date" name="registration_date" class="form-control" required />
                                    </div>
                                </div>
                                <br>

                                <!-- Section: PERSONAL INFORMATION -->
                                <div class="row g-6">
                                    <div class="col-12">
                                        <h6 class="fw-bold mb-2">1. PERSONAL INFORMATION</h6>
                                    </div>
                                </div>
                                <br>

                                <div class="row g-6">
                                    <div class="col-md-3">
                                        <label class="form-label" for="last_name">Last Name :</label>
                                        <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Last Name" />
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="first_name">First Name :</label>
                                        <input type="text" id="first_name" name="first_name" class="form-control" placeholder="First Name" />
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="middle_name">Middle Name :</label>
                                        <input type="text" id="middle_name" name="middle_name" class="form-control" placeholder="Middle Name" />
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="extension_name">Extension Name :</label>
                                        <input type="text" id="extension_name" name="extension_name" class="form-control" placeholder="Extension Name" />
                                    </div>
                                </div>
                                <br>

                                <div class="row g-6">
                                    <div class="col-md-6">
                                        <label class="form-label" for="relation_to_head">Relation To Head :</label>
                                        <input type="text" id="relation_to_head" name="relation_to_head" class="form-control" placeholder="Relation To Head" />
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="sex">Sex :</label>
                                        <select id="sex" name="sex" class="form-control">
                                            <option value="">- select sex -</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <br>

                                <div class="row g-6">
                                    <div class="col-md-3">
                                        <label class="form-label" for="birth_date">Birth Date :</label>
                                        <input type="date" id="birth_date" name="birth_date" class="form-control" />
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label" for="age">Age :</label>
                                        <input type="number" id="age" name="age" class="form-control" placeholder="Age" min="0" />
                                    </div>
                                    <div class="col-md-3">
                                        <label for="civil_status" class="form-label">Civil Status :</label>
                                        <br>
                                        <select id="civil_status" name="civil_status" class="select2 form-select" data-allow-clear="true">
                                            <option value="">- select civil status -</option>
                                            <option value="Single">Single</option>
                                            <option value="Married">Married</option>
                                            <option value="Widowed">Widowed</option>
                                            <option value="Separated">Separated</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="occupation">Occupation :</label>
                                        <input type="text" id="occupation" name="occupation" class="form-control" placeholder="Occupation" />
                                    </div>
                                </div>
                                <br>

                                <div class="row g-6">
                                    <div class="col-md-6">
                                        <label class="form-label" for="contact_number">Contact Number :</label>
                                        <input type="text" id="contact_number" name="contact_number" class="form-control" placeholder="Mobile/Phone number" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="complete_address">Complete Address :</label>
                                        <input type="text" id="complete_address" name="complete_address" class="form-control" placeholder="Complete home address" />
                                    </div>
                                </div>
                                <br>

                                <!-- Section: EMERGENCY CONTACT -->
                                <div class="row g-6">
                                    <div class="col-12">
                                        <h6 class="fw-bold mb-2">2. EMERGENCY CONTACT</h6>
                                    </div>
                                </div>
                                <br>

                                <div class="row g-6">
                                    <div class="col-md-6">
                                        <label class="form-label" for="emergency_contact_person">Emergency Contact Person :</label>
                                        <input type="text" id="emergency_contact_person" name="emergency_contact_person" class="form-control" placeholder="Name of contact person" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="emergency_contact_number">Emergency Contact Number :</label>
                                        <input type="text" id="emergency_contact_number" name="emergency_contact_number" class="form-control" placeholder="Contact number" />
                                    </div>
                                </div>
                                <br>

                                <!-- Section: SPECIAL CONDITIONS -->
                                <div class="row g-6">
                                    <div class="col-12">
                                        <h6 class="fw-bold mb-2">3. SPECIAL CONDITIONS</h6>
                                    </div>
                                </div>
                                <br>

                                <div class="row g-6">
                                    <div class="col-md-12">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="condition_pregnant" name="condition_pregnant" value="1" />
                                            <label class="form-check-label" for="condition_pregnant">Pregnant</label>
                                        </div>
                                        <br>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="condition_lactating" name="condition_lactating" value="1" />
                                            <label class="form-check-label" for="condition_lactating">Lactating</label>
                                        </div>
                                        <br>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="condition_elderly" name="condition_elderly" value="1" />
                                            <label class="form-check-label" for="condition_elderly">Elderly</label>
                                        </div>
                                        <br>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="condition_pwd" name="condition_pwd" value="1" />
                                            <label class="form-check-label" for="condition_pwd">PWD</label>
                                        </div>
                                        <br>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="condition_4ps" name="condition_4ps" value="1" />
                                            <label class="form-check-label" for="condition_4ps">4Ps Beneficiary</label>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <div class="row g-6">
                                    <div class="col-md-4">
                                        <label class="form-label" for="pwd_type">PWD Type (if applicable) :</label>
                                        <select id="pwd_type" name="pwd_type" class="select2 form-select" data-allow-clear="true">
                                            <option value="">- select or specify -</option>
                                            <option value="Mobility">Mobility</option>
                                            <option value="Visual">Visual</option>
                                            <option value="Hearing">Hearing</option>
                                            <option value="Speech">Speech</option>
                                            <option value="Cognitive">Cognitive</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <br>

                                <!-- Section: MEDICAL INFORMATION -->
                                <div class="row g-6">
                                    <div class="col-12">
                                        <h6 class="fw-bold mb-2">4. MEDICAL INFORMATION</h6>
                                    </div>
                                </div>
                                <br>

                                <div class="row g-6">
                                    <div class="col-md-6">
                                        <label for="health_status" class="form-label">Health Status :</label>
                                        <select id="health_status" name="health_status" class="select2 form-select" data-allow-clear="true">
                                            <option value="">- select health status -</option>
                                            <option value="Good">Good</option>
                                            <option value="With illness">With illness</option>
                                            <option value="Under medication">Under medication</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="emergency_medical_condition" class="form-label">Emergency Medical Condition :</label>
                                        <select id="emergency_medical_condition" name="emergency_medical_condition" class="select2 form-select" data-allow-clear="true">
                                            <option value="">- select condition -</option>
                                            <option value="None">None</option>
                                            <option value="Hypertension">Hypertension</option>
                                            <option value="Diabetes">Diabetes</option>
                                            <option value="Asthma">Asthma</option>
                                            <option value="Heart Disease">Heart Disease</option>
                                            <option value="Kidney Disease">Kidney Disease</option>
                                            <option value="Epilepsy">Epilepsy</option>
                                            <option value="Allergy">Allergy</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <br>

                                <div class="row g-6">
                                    <div class="col-md-12">
                                        <label class="form-label" for="medications_taken">Medications Taken :</label>
                                        <textarea id="medications_taken" name="medications_taken" class="form-control" rows="2" placeholder="List current medications (e.g., Amlodipine 5mg once daily, Metformin 500mg twice daily)"></textarea>
                                    </div>
                                </div>
                                <br>

                                <div class="row g-6">
                                    <div class="col-md-12">
                                        <label class="form-label" for="known_allergies">Known Allergies :</label>
                                        <textarea id="known_allergies" name="known_allergies" class="form-control" rows="2" placeholder="List known allergies (e.g., Penicillin, Peanuts, Dust, Latex)"></textarea>
                                    </div>
                                </div>
                                <br>

                                <!-- Section: EVACUATION DETAILS -->
                                <div class="row g-6">
                                    <div class="col-12">
                                        <h6 class="fw-bold mb-2">5. EVACUATION DETAILS</h6>
                                    </div>
                                </div>
                                <br>

                                <div class="row g-6">
                                    <div class="col-md-4">
                                        <label for="evacuation_center_id" class="form-label">Evacuation Center Assigned :</label>
                                        <br>
                                        <?php
                                            require_once __DIR__ . '/../../models/centers.model.php';
                                            $activeCenters = ModelCenters::mdlGetActiveCenters();
                                        ?>
                                        <select id="evacuation_center_id" name="evacuation_center_id" class="select2 form-select" data-allow-clear="true" required>
                                            <option value="">- select evacuation center -</option>
                                            <?php
                                            foreach ($activeCenters as $center) {
                                                echo '<option value="' . htmlspecialchars($center['center_id']) . '">' . htmlspecialchars($center['center_name']) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="arrival_date">Arrival Date :</label>
                                        <input type="date" id="arrival_date" name="arrival_date" class="form-control" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="departure_date">Departure Date :</label>
                                        <input type="date" id="departure_date" name="departure_date" class="form-control" />
                                    </div>
                                </div>
                                <br>

                                <div class="row g-6">
                                    <div class="col-md-6">
                                        <label for="evacuee_status" class="form-label">Status :</label>
                                        <select id="evacuee_status" name="evacuee_status" class="select2 form-select" data-allow-clear="true">
                                            <option value="">- select status -</option>
                                            <option value="Active">Active in Center</option>
                                            <option value="Transferred">Transferred to Another Center</option>
                                            <option value="Departed">Departed/Returned Home</option>
                                            <option value="Missing">Missing</option>
                                            <option value="Deceased">Deceased</option>
                                        </select>
                                    </div>
                                </div>
                                <br>

                                <!-- Action Buttons -->
                            <div class="demo-inline-spacing">
                                <button type="button" class="btn btn-outline-primary" id="btn-new">
                                    <span class="icon-xs icon-base ti tabler-file me-2"></span>New
                                </button>
                                <button type="button" class="btn btn-outline-success" id="btn-save">
                                    <span class="icon-xs icon-base ti tabler-star me-2"></span>Save
                                </button>
                                <button type="button" class="btn btn-outline-info" id="btn-search"
                                    data-bs-toggle="modal" data-bs-target="#modal-search-evacuee">
                                    <span class="icon-xs icon-base ti tabler-search me-2"></span>Search
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="btn-back">
                                    <span class="icon-xs icon-base ti tabler-arrow-left me-2"></span>Back
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="btn-print">
                                    <span class="icon-xs icon-base ti tabler-printer me-2"></span>Print Form
                                </button>
                            </div>

                                <!-- Search Modal -->
                                <div class="modal fade" id="modal-search-evacuee" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalCenterTitle">EVACUEE LIST</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <table class="table table-bordered table-hover evacueeListTable">
                                                    <thead>
                                                        <tr>
                                                            <th>Last Name</th>
                                                            <th>First Name</th>
                                                            <th>Sex</th>
                                                            <th>Age</th>
                                                            <th>Civil Status</th>
                                                            <th>Contact Number</th>
                                                            <th>Status</th>
                                                            <th>Arrival Date</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="table-border-bottom-0">
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Search Modal -->

                                

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>