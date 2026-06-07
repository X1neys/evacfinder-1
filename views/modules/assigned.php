<?php
// Assigned center page with centered layout, warm-blue cards, and dark-mode overrides
$assignedCenter = ModelCenters::mdlGetCenterByAssignedUser($_SESSION['userid'] ?? 0);
?>

<div class="assigned-wrap mb-4">
  <div class="text-center mb-4">
    <h1 id="assignedCenterTitle" class="assigned-title"><?php echo htmlspecialchars($assignedCenter['center_name'] ?? 'Assigend'); ?></h1>
  </div>

  <div class="row justify-content-center gx-3 gy-3">
    <div class="col-xl-10">
      <div class="stats-grid mb-3">
        <div class="stat card-stub">
          <div class="label">Capacity</div>
          <div id="statCapacity" class="value">—</div>
          <div class="hint">max occupants</div>
        </div>
        <div class="stat card-stub">
          <div class="label">Occupants</div>
          <div id="statOccupants" class="value">—</div>
          <div class="hint">currently inside</div>
        </div>
        <div class="stat card-stub">
          <div class="label">Available slots</div>
          <div id="statAvailable" class="value">—</div>
          <div class="hint">slots remaining</div>
        </div>
        <div class="stat card-stub">
          <div class="label">Status</div>
          <div id="statStatus" class="value">—</div>
          <div class="hint">Current center status</div>
        </div>
      </div>

      <div class="row gx-3 mb-3">
        <div class="col-md-6">
          <div class="panel panel-details">
            <div class="panel-header">DETAILS</div>
            <div class="panel-body">
              <dl class="detail-list">
                <div class="d-flex mb-2"><div class="key">Location</div><div id="detailLocation" class="val">—</div></div>
                <div class="d-flex mb-2"><div class="key">Type</div><div id="detailType" class="val">—</div></div>
                <div class="d-flex mb-2"><div class="key">Status</div><div id="detailStatus" class="val">—</div></div>
                <div class="d-flex mb-2"><div class="key">Opened</div><div id="detailOpened" class="val">—</div></div>
                <div class="d-flex mb-0"><div class="key">Contact</div><div id="detailContact" class="val">—</div></div>
              </dl>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="panel panel-amenities">
            <div class="panel-header">Assigned LGU Officers</div>
            <div id="amenitiesList" class="panel-body amenities-list">
              <!-- assigned LGU info -->
            </div>
          </div>
        </div>
      </div>

      <div class="panel panel-occupants">
        <div class="panel-header d-flex justify-content-between align-items-center">
          <div>
            <strong>Occupants</strong>
            <div class="small text-muted">Register people in the occupant list below</div>
          </div>
          <button id="btnAddEvacuee" class="btn btn-outline-dark btn-sm">+ Register person</button>
        </div>
        <div id="occupantsList" class="panel-body occupants-body">
          <div class="occupant-empty text-center text-muted">
            <div class="occupant-icon">👥</div>
            <div class="mt-2">No occupants found</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  :root { --page-bg:#f5f7ff; --panel:#d9e5ff; --panel-strong:#c3d5ff; --text:#111; --muted:#617095; --border:#c8d3e7; }
  body.theme-dark .assigned-wrap { --page-bg:#0f1218; --panel:#1b1f29; --panel-strong:#212635; --text:#f7f9ff; --muted:rgba(255,255,255,0.65); --border:rgba(255,255,255,0.08); }

  .assigned-wrap { color: var(--text); }
  .assigned-title { font-size:32px; margin:0; font-weight:700; color: var(--text); }

  .stats-grid{ display:grid; grid-template-columns:repeat(4,1fr); gap:18px; }
  .card-stub{ background: var(--panel); padding:24px; border-radius:18px; border:1px solid var(--border); }
  .card-stub .label{ color: var(--muted); font-size:13px; text-transform:uppercase; letter-spacing:.04em; }
  .card-stub .value{ font-size:34px; font-weight:700; margin-top:10px; color: var(--text); }
  .card-stub .hint{ color: var(--muted); font-size:13px; margin-top:8px; }

  .panel{ background: var(--panel); border-radius:18px; border:1px solid var(--border); overflow:hidden; }
  .panel-header{ padding:16px 20px; border-bottom:1px solid var(--border); font-weight:700; color: var(--muted); font-size:13px; letter-spacing:.05em; }
  .panel-body{ padding:20px; }
  .detail-list .key{ width:32%; color: var(--muted); font-size:14px; }
  .detail-list .val{ flex:1; color: var(--text); font-weight:600; font-size:14px; }

  .amenities-list .badge{ background: rgba(13,72,176,0.12); color: var(--text); border:1px solid rgba(13,72,176,0.18); padding:.45rem .75rem; border-radius:999px; margin-right:8px; margin-bottom:8px; display:inline-flex; align-items:center; gap:8px; }
  .assigned-person-name{ font-size:16px; font-weight:700; color: var(--text); margin-bottom:10px; }
  .assigned-person-contact{ font-size:14px; color: var(--muted); }
  .assigned-person-none{ font-size:14px; color: var(--muted); }

  .panel-occupants .panel-header{ padding:18px 20px; }
  .btn-outline-dark{ color: var(--text); border-color: rgba(0,0,0,0.15); background: rgba(255,255,255,0.9); }
  .btn-outline-dark:hover{ background: rgba(255,255,255,1); }
  body.theme-dark .btn-outline-dark{ color:#fff; border-color: rgba(255,255,255,0.18); background: rgba(255,255,255,0.08); }

  .occupants-body{ min-height:200px; padding:24px; display:grid; gap:18px; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
  .occupant-card{ background: #b8d0ff; border:1px solid rgba(35,90,175,0.18); border-radius:18px; padding:18px; display:flex; flex-direction:column; gap:10px; min-height:140px; }
  .occupant-card .occupant-name{ font-size:1rem; font-weight:700; color: #111; }
  .occupant-card .occupant-info{ font-size:0.86rem; color: rgba(17,17,17,0.72); }
  .occupant-card .occupant-status{ margin-top:auto; font-size:0.78rem; color: rgba(17,17,17,0.62); text-transform: uppercase; letter-spacing:0.08em; }
  .occupant-empty{ grid-column: 1/-1; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:12px; padding:40px 0; }
  .occupant-empty .occupant-icon{ font-size:40px; opacity:0.4; }
  .occupant-empty .text-muted{ color: var(--muted); }

  .swal2-add-person-popup{ width: min(600px, calc(100vw - 32px)) !important; max-width: 600px !important; background: #1f325f !important; color:#f7f9ff !important; border-radius: 18px; border: 1px solid rgba(255,255,255,0.08); }
  .swal2-add-person-popup .swal2-html-container{ padding:0 !important; }
  .add-person-form{ padding:20px; }
  .form-row{ display:grid; grid-template-columns: repeat(3, 1fr); gap:12px; margin-bottom:12px; }
  .form-row-3col{ grid-template-columns: repeat(3, 1fr); }
  .form-row-2col{ grid-template-columns: repeat(2, 1fr); }
  .form-row-full{ grid-template-columns: 1fr; }
  .form-group{ display:flex; flex-direction:column; }
  .form-group label{ font-size:12px; font-weight:500; color:#cfd7ef; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.02em; }
  .form-group input, .form-group select{ background:#15264f; border:1px solid rgba(255,255,255,0.10); color:#f7f9ff; padding:8px 10px; border-radius:8px; font-size:13px; width:100%; }
  .form-group input::placeholder{ color:rgba(255,255,255,0.35); }
  .form-group input:focus, .form-group select:focus{ outline:none; box-shadow:0 0 0 2px rgba(59,130,246,0.2); border-color:rgba(59,130,246,0.4); }
  .swal2-add-person-popup .swal2-confirm{ background:#1d4ed8 !important; color:#fff !important; border:none !important; }
  .swal2-add-person-popup .swal2-confirm:hover{ background:#1e40af !important; }
  body.theme-dark .form-group input, body.theme-dark .form-group select{ background:#102253; }

  @media (max-width: 991px){ .stats-grid{ grid-template-columns:repeat(2,1fr); } }
  @media (max-width: 767px){ .stats-grid{ grid-template-columns:1fr; } .swal2-add-person-popup{ width: auto !important; } .form-row{ grid-template-columns: repeat(2, 1fr); } .form-row-2col{ grid-template-columns: 1fr; } }

  @media (max-width: 991px){ .stats-grid{ grid-template-columns:repeat(2,1fr); } }
  @media (max-width: 767px){ .stats-grid{ grid-template-columns:1fr; } .swal2-add-person-popup{ width: auto !important; max-width: 100% !important; } .swal-form{ grid-template-columns:1fr; } .swal-field-half, .swal-field-full{ grid-column: span 1; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function(){
  var initialAssignedCenterId = <?php echo json_encode($assignedCenter['center_id'] ?? null); ?>;
  function setText(id, text){ var el=document.getElementById(id); if(el) el.textContent = text; }

  function renderCenter(report){
    if(!report || !report.center) return;
    var c = report.center;
    setText('assignedCenterTitle', c.center_name || 'Assigned center');
    setText('statCapacity', c.capacity || '0');
    var total = (report.statistics && report.statistics.total_evacuees) ? report.statistics.total_evacuees : (c.current_occupants||0);
    setText('statOccupants', total);
    var avail = (c.capacity||0) - total; setText('statAvailable', avail>=0?avail:0);
    // show textual center status instead of fill percent
    setText('statStatus', c.status || '-');
    var locationText = c.address || [c.barangay,c.city,c.province].filter(Boolean).join(', ');
    setText('detailLocation', locationText || '-');
    setText('detailType', c.category||c.type||'-');
    setText('detailStatus', c.status || '-');
    var openedText = c.date_established || c.opened || '-';
    setText('detailOpened', openedText);
    var contactText = c.contact_person ? c.contact_person + (c.contact_number ? ' • ' + c.contact_number : '') : (c.contact_number || c.alternate_contact || '-');
    setText('detailContact', contactText);

    var am=document.getElementById('amenitiesList');
    if(am){
      am.innerHTML='';
      var assignedList = c.assigned_lgus && c.assigned_lgus.length ? c.assigned_lgus : [];
      if(assignedList.length){
        assignedList.forEach(function(lgu){
          var title = document.createElement('div');
          title.className = 'assigned-person-name';
          title.textContent = lgu.assigned_lgu_name || 'LGU officer';
          var contact = document.createElement('div');
          contact.className = 'assigned-person-contact';
          contact.textContent = 'Contact: ' + (lgu.assigned_lgu_phone || lgu.assigned_lgu_email || 'N/A');
          am.appendChild(title);
          am.appendChild(contact);
        });
      } else {
        var noLgu = document.createElement('div');
        noLgu.className = 'assigned-person-none';
        noLgu.textContent = 'No LGU officer assigned to this center yet.';
        am.appendChild(noLgu);
      }
    }

    var occ=document.getElementById('occupantsList'); if(occ){ if((report.evacuees||[]).length){ occ.innerHTML = report.evacuees.map(function(e){ return '<div class="occupant-card">' +
        '<div class="occupant-name">'+ (e.first_name+' '+(e.middle_name?e.middle_name+' ':'')+e.last_name + (e.extension_name ? ' '+e.extension_name : '')) +'</div>' +
        '<div class="occupant-info"><strong>Contact:</strong> '+ (e.contact_number || '-') +'</div>' +
        '<div class="occupant-info"><strong>Email:</strong> '+ (e.email || '-') +'</div>' +
        '<div class="occupant-info"><strong>Address:</strong> '+ (e.complete_address || '-') +'</div>' +
        '<div class="occupant-status">'+ (e.evacuee_status || 'Active') +'</div>' +
      '</div>'; }).join(''); } else { occ.innerHTML = '<div class="occupant-empty"><div class="occupant-icon">👥</div><div class="text-muted">No occupants found</div></div>'; } }
  }

  function loadCenter(id){ if(!id) return; fetch('ajax/get_center_report.ajax.php?center_id='+encodeURIComponent(id), {credentials:'same-origin'}).then(function(response){ return response.json(); }).then(function(data){ if(data && data.success){ renderCenter(data.report); }}); }

  // Prefer fetching live assignment for the current logged-in user so changes reflect immediately.
  function fetchMyAssignment(){
    fetch('ajax/get_my_assignment.ajax.php', {credentials:'same-origin'})
      .then(function(r){ return r.json(); })
      .then(function(j){
        if(j && j.success){
          if(j.center_id){
            loadCenter(j.center_id);
            return;
          }
        }
        // fallback to server-injected value
        if (initialAssignedCenterId) {
          loadCenter(initialAssignedCenterId);
        } else {
          setText('statCapacity', '0');
          setText('statOccupants', '0');
          setText('statAvailable', '0');
          setText('statStatus', 'No assignment');
          var occ = document.getElementById('occupantsList');
          if (occ) {
            occ.innerHTML = '<div class="occupant-empty"><div class="occupant-icon">👥</div><div class="text-muted">No assigned center yet.</div></div>';
          }
        }
      })
      .catch(function(){
        if (initialAssignedCenterId) { loadCenter(initialAssignedCenterId); }
      });
  }

  fetchMyAssignment();

  function showAddPersonPopup(){
    Swal.fire({
      title: 'Add Person',
      customClass: { popup: 'swal2-add-person-popup' },
      html:
        '<div class="add-person-form">' +
        '  <div class="form-row">' +
        '    <div class="form-group"><label>First Name</label><input id="swal-fname" type="text" placeholder="John"></div>' +
        '    <div class="form-group"><label>Last Name</label><input id="swal-lname" type="text" placeholder="Doe"></div>' +
        '    <div class="form-group"><label>M.I.</label><input id="swal-mi" type="text" placeholder="M" maxlength="1"></div>' +
        '  </div>' +
        '  <div class="form-row form-row-3col">' +
        '    <div class="form-group"><label>Extension</label><input id="swal-ext" type="text" placeholder="Jr."></div>' +
        '    <div class="form-group"><label>Date of Birth</label><input id="swal-dob" type="date"></div>' +
        '    <div class="form-group"><label>Sex</label><select id="swal-sex"><option value="">Select</option><option value="Male">Male</option><option value="Female">Female</option></select></div>' +
        '  </div>' +
        '  <div class="form-row form-row-2col">' +
        '    <div class="form-group"><label>Email</label><input id="swal-email" type="email" placeholder="example@mail.com"></div>' +
        '    <div class="form-group"><label>Contact Number</label><input id="swal-contact" type="tel" placeholder="09XX-XXX-XXXX"></div>' +
        '  </div>' +
        '  <div class="form-row form-row-full">' +
        '    <div class="form-group"><label>Address</label><input id="swal-address" type="text" placeholder="Street, Barangay, City, Province"></div>' +
        '  </div>' +
        '</div>',
      focusConfirm: false,
      showCancelButton: true,
      confirmButtonText: 'Save',
      cancelButtonText: 'Cancel',
      preConfirm: function(){
        var fname = document.getElementById('swal-fname').value.trim();
        var lname = document.getElementById('swal-lname').value.trim();
        var contact = document.getElementById('swal-contact').value.trim();
        var address = document.getElementById('swal-address').value.trim();
        var missingFields = [];

        if(!fname) missingFields.push('First name');
        if(!lname) missingFields.push('Last name');
        if(!contact) missingFields.push('Contact number');
        if(!address) missingFields.push('Address');

        if(missingFields.length){
          var message;
          if(missingFields.length === 1){
            message = missingFields[0] + ' is required.';
          } else if(missingFields.length === 2){
            message = missingFields.join(' and ') + ' are required.';
          } else {
            message = 'Please fill in ' + missingFields.slice(0, -1).join(', ') + ', and ' + missingFields.slice(-1) + '.';
          }
          Swal.showValidationMessage(message);
          return false;
        }

        return {
          fname:fname,
          lname:lname,
          mi:document.getElementById('swal-mi').value.trim(),
          ext:document.getElementById('swal-ext').value.trim(),
          dob:document.getElementById('swal-dob').value,
          sex:document.getElementById('swal-sex').value,
          email:document.getElementById('swal-email').value.trim(),
          contact:contact,
          address:address
        };
      }
    }).then(function(result){
      if(result.isConfirmed && result.value){
        var data = new FormData();
        data.append('trans_type', 'New');
        data.append('registration_date', new Date().toISOString().slice(0,10));
        data.append('first_name', result.value.fname);
        data.append('last_name', result.value.lname);
        data.append('middle_name', result.value.mi);
        data.append('extension_name', result.value.ext);
        data.append('birth_date', result.value.dob);
        data.append('sex', result.value.sex);
        data.append('email', result.value.email);
        data.append('contact_number', result.value.contact);
        data.append('complete_address', result.value.address);
        data.append('evacuation_center_id', assignedCenterId);
        data.append('evacuee_status', 'Active');

        fetch('ajax/evacuees_save.ajax.php', { method:'POST', body:data, credentials:'same-origin' })
        .then(function(r){ return r.text(); })
        .then(function(txt){
          if(txt.trim() === 'error' || txt.toLowerCase().includes('error')){
            Swal.fire({ icon:'error', title:'Error', text:'Failed to save evacuee.' });
          } else {
            Swal.fire({ icon:'success', title:'Success', text:'Evacuee added.', timer: 1100, showConfirmButton: false }).then(function(){
              window.location.reload();
            });
          }
        })
        .catch(function(){ Swal.fire({ icon:'error', title:'Error', text:'Server error.' }); });
      }
    });
  }

  var addBtn = document.getElementById('btnAddEvacuee');
  if(addBtn){ addBtn.addEventListener('click', showAddPersonPopup); }

  var assignedCenterId = <?php echo json_encode($assignedCenter['center_id'] ?? ''); ?>;
  if(assignedCenterId){ loadCenter(assignedCenterId); }
});
</script>
