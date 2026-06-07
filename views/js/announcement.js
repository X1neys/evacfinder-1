$(function () {

    // ── Init ──────────────────────────────────────────────────────────────
    resetNewForm();
    loadAnnouncementList();

    // ── Helpers ───────────────────────────────────────────────────────────
    function escapeHtml(str) {
        if (!str) return '';
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function resetNewForm() {
        $("#ann_type").val('').trigger('change');
        $("#title").val('');
        $("#ann_desc").val('');
        $("#trans_type").val('New');
        $("#announcement_id").val('');
        $("#announcementError").hide().text('');
    }

    // ── Drawer open / close ───────────────────────────────────────────────
    function openDrawer(data) {
        $("#drawerIdText").text(data.id);
        $("#edit_ann_type").val(data.type).trigger('change');
        $("#edit_title").val(data.title);
        $("#edit_ann_desc").val(data.desc);

        // Store edit target
        $("#editDrawer").data('edit-id', data.id);

        $("#drawerOverlay").addClass('active');
        $("#editDrawer").addClass('open');
        $("#edit_ann_type").focus();
    }

    function closeDrawer() {
        $("#editDrawer").removeClass('open');
        $("#drawerOverlay").removeClass('active');
    }

    $("#btnCloseDrawer, #btnDrawerCancel").on('click', closeDrawer);
    $("#drawerOverlay").on('click', closeDrawer);

    // Close on Escape key
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') closeDrawer();
    });

    // ── Load table ────────────────────────────────────────────────────────
    function loadAnnouncementList(searchTerm = '') {
        let url = "ajax/get_announcements.ajax.php";
        if (searchTerm && searchTerm.trim() !== '') {
            url += "?search=" + encodeURIComponent(searchTerm.trim());
        }

        $.ajax({
            url: url,
            method: "GET",
            dataType: "json",
            success: function (data) {
                let tbody = $(".table tbody");
                tbody.empty();

                if (data && data.length > 0) {
                    $.each(data, function (index, ann) {
                        let title = escapeHtml(ann.ann_title);
                        if (searchTerm && searchTerm.trim() !== '') {
                            let regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
                            title = title.replace(regex, '<mark style="background-color:#fff3cd;padding:0 2px;border-radius:3px;">$1</mark>');
                        }

                        let desc = escapeHtml(ann.ann_desc.substring(0, 100)) + (ann.ann_desc.length > 100 ? '…' : '');

                        let row = `
                            <tr>
                                <td>${escapeHtml(ann.announcement_id)}</td>
                                <td>${title}</td>
                                <td><span class="badge bg-label-primary">${escapeHtml(ann.ann_type)}</span></td>
                                <td>${desc}</td>
                                <td>${escapeHtml(ann.encodedby)}</td>
                                <td>${escapeHtml(ann.date_created)}</td>
                                <td>
                                    <button type="button"
                                            class="btn btn-sm btn-primary btn-edit"
                                            data-id="${escapeHtml(ann.announcement_id)}"
                                            data-title="${escapeHtml(ann.ann_title)}"
                                            data-type="${escapeHtml(ann.ann_type)}"
                                            data-desc="${escapeHtml(ann.ann_desc)}">
                                        <i class="ti tabler-edit"></i> Edit
                                    </button>
                                </td>
                            </tr>`;
                        tbody.append(row);
                    });
                } else {
                    let msg = searchTerm
                        ? `No announcements found matching "${escapeHtml(searchTerm)}"`
                        : "No announcements found";
                    tbody.append(`<tr><td colspan="7" class="text-center">${msg}</td></tr>`);
                }
            },
            error: function () {
                $(".table tbody").html('<tr><td colspan="7" class="text-center text-danger">Error loading announcements</td></tr>');
            }
        });
    }

    // ── Save new announcement ─────────────────────────────────────────────
    function saveAnnouncement(formData) {
        $.ajax({
            url:         "ajax/announcement_save.ajax.php",
            method:      "POST",
            data:        formData,
            cache:       false,
            contentType: false,
            processData: false,
            dataType:    "text",
            success: function (answer) {
                if (answer === 'error') {
                    Swal.fire({ title: 'Error saving announcement!', icon: 'error', confirmButtonText: 'Got it', customClass: { confirmButton: 'btn btn-danger' }, buttonsStyling: false });
                } else if (answer === 'existing') {
                    Swal.fire({ title: 'Announcement already exists!', icon: 'warning', confirmButtonText: 'Got it', customClass: { confirmButton: 'btn btn-warning' }, buttonsStyling: false });
                } else {
                    Swal.fire({ title: 'Announcement Successfully Saved!', icon: 'success', confirmButtonText: 'Got it', customClass: { confirmButton: 'btn btn-success waves-effect waves-light' }, buttonsStyling: false })
                        .then(function (r) { if (r.value) { resetNewForm(); loadAnnouncementList(); } });
                }
            },
            error: function () {
                Swal.fire({ title: 'Oops. Something went wrong!', icon: 'error', confirmButtonText: 'Got it', customClass: { confirmButton: 'btn btn-danger waves-effect waves-light' }, buttonsStyling: false });
            }
        });
    }

    // ── Update via drawer ─────────────────────────────────────────────────
    function updateAnnouncement() {
        let id   = $("#editDrawer").data('edit-id');
        let type = $("#edit_ann_type").val();
        let title = $("#edit_title").val().trim();
        let desc  = $("#edit_ann_desc").val().trim();

        // Validate
        let missing = [];
        if (!type)  missing.push("Type of Announcement");
        if (!title) missing.push("Title");
        if (!desc)  missing.push("Description");

        if (missing.length > 0) {
            Swal.fire({
                title: 'Required Fields Missing',
                icon: 'warning',
                html: '<div style="text-align:left;margin-left:20px;"><p>The following fields are required:</p><ul>' +
                      missing.map(f => `<li>${f}</li>`).join('') + '</ul></div>',
                confirmButtonText: 'OK',
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
            });
            return;
        }

        Swal.fire({
            title: 'Update Announcement?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, update it',
            customClass: { confirmButton: 'btn btn-primary', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
        }).then(function (result) {
            if (!result.value) return;

            let formData = new FormData();
            formData.append("trans_type",       "Edit");
            formData.append("announcement_id",  id);
            formData.append("ann_type",         type);
            formData.append("ann_title",        title);
            formData.append("ann_desc",         desc);
            formData.append("encodedby",        $("#encodedby").val());

            $.ajax({
                url:         "ajax/announcement_save.ajax.php",
                method:      "POST",
                data:        formData,
                cache:       false,
                contentType: false,
                processData: false,
                dataType:    "text",
                success: function (answer) {
                    if (answer === 'updated' || answer !== 'error') {
                        closeDrawer();
                        Swal.fire({ title: 'Announcement Successfully Updated!', icon: 'success', confirmButtonText: 'Got it', customClass: { confirmButton: 'btn btn-success waves-effect waves-light' }, buttonsStyling: false })
                            .then(function (r) { if (r.value) loadAnnouncementList(); });
                    } else {
                        Swal.fire({ title: 'Error updating announcement!', icon: 'error', confirmButtonText: 'Got it', customClass: { confirmButton: 'btn btn-danger' }, buttonsStyling: false });
                    }
                },
                error: function () {
                    Swal.fire({ title: 'Oops. Something went wrong!', icon: 'error', confirmButtonText: 'Got it', customClass: { confirmButton: 'btn btn-danger waves-effect waves-light' }, buttonsStyling: false });
                }
            });
        });
    }

    // ── Event: New Announcement save ──────────────────────────────────────
    $("#btn-save").on('click', function (e) {
        e.preventDefault();

        let required = [
            { id: "#ann_type", label: "Type of Announcement" },
            { id: "#title",    label: "Title" },
            { id: "#ann_desc", label: "Description" },
        ];

        let missing = required.filter(f => !$(f.id).val() || $(f.id).val().trim() === '').map(f => f.label);

        if (missing.length > 0) {
            Swal.fire({
                title: 'Required Fields Missing',
                icon: 'warning',
                html: '<div style="text-align:left;margin-left:20px;"><p>The following fields are required:</p><ul>' +
                      missing.map(f => `<li>${f}</li>`).join('') + '</ul></div>',
                confirmButtonText: 'OK',
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
            });
            return;
        }

        Swal.fire({
            title: 'Save Announcement?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            customClass: { confirmButton: 'btn btn-primary', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
        }).then(function (result) {
            if (!result.value) return;
            let fd = new FormData();
            fd.append("trans_type", "New");
            fd.append("encodedby",  $("#encodedby").val());
            fd.append("ann_title",  $("#title").val());
            fd.append("ann_type",   $("#ann_type").val());
            fd.append("ann_desc",   $("#ann_desc").val());
            saveAnnouncement(fd);
        });
    });

    // ── Event: Edit button in table ───────────────────────────────────────
    $(document).on('click', '.btn-edit', function () {
        openDrawer({
            id:    $(this).data('id'),
            title: $(this).data('title'),
            type:  $(this).data('type'),
            desc:  $(this).data('desc'),
        });
    });

    // ── Event: Drawer Update button ───────────────────────────────────────
    $("#btnDrawerSave").on('click', updateAnnouncement);

    // ── Event: Search ─────────────────────────────────────────────────────
    let searchTimeout;
    $("#searchAnnouncement").on("keyup", function () {
        clearTimeout(searchTimeout);
        let term = $(this).val();
        searchTimeout = setTimeout(() => loadAnnouncementList(term), 300);
    });
    $("#searchAnnouncement").on("keypress", function (e) {
        if (e.which === 13) {
            e.preventDefault();
            clearTimeout(searchTimeout);
            loadAnnouncementList($(this).val());
        }
    });

});