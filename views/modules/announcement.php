<style>
    .content-body {
        min-height: unset !important;
    }
    .announcement-card {
        height: fit-content;
        max-height: calc(100vh - 130px);
        display: flex;
        flex-direction: column;
    }
    .announcement-card .card-body {
        overflow: hidden;
    }
    .announcement-card .row.g-0 {
        height: 100%;
    }
    .announcement-card .form-panel {
        overflow-y: auto;
    }
    .announcement-card .col-12.col-lg-8 {
        display: flex;
        flex-direction: column;
    }
    .announcement-table-wrapper {
        flex: 1;
        overflow-y: auto;
        min-height: 0;
        max-height: calc(100vh - 230px);
    }
    .announcement-table-wrapper thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background-color: #f8f9fa !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }
    .form-panel {
        border-right: 1px solid #e0e0e0;
    }

    /* ── Edit Drawer ── */
    .edit-drawer-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.35);
        z-index: 1040;
        transition: opacity 0.25s ease;
    }
    .edit-drawer-overlay.active {
        display: block;
    }
    .edit-drawer {
        position: fixed;
        top: 0;
        right: -440px;
        width: 420px;
        height: 100vh;
        background: #fff;
        z-index: 1050;
        box-shadow: -4px 0 24px rgba(0,0,0,0.15);
        display: flex;
        flex-direction: column;
        transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .edit-drawer.open {
        right: 0;
    }
    .edit-drawer-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid #e0e0e0;
        background: #f8f9fa;
        flex-shrink: 0;
    }
    .edit-drawer-header .drawer-title {
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #555;
        margin: 0;
    }
    .edit-drawer-header .btn-close-drawer {
        background: none;
        border: none;
        font-size: 1.2rem;
        color: #888;
        cursor: pointer;
        line-height: 1;
        padding: 2px 6px;
        border-radius: 4px;
        transition: background 0.15s;
    }
    .edit-drawer-header .btn-close-drawer:hover {
        background: #e0e0e0;
        color: #333;
    }
    .edit-drawer-body {
        flex: 1;
        overflow-y: auto;
        padding: 24px 20px;
    }
    .edit-drawer-footer {
        padding: 14px 20px;
        border-top: 1px solid #e0e0e0;
        background: #f8f9fa;
        display: flex;
        gap: 10px;
        flex-shrink: 0;
    }
    .id-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #e8f0fe;
        color: #3c5fc9;
        border: 1px solid #c5d3f8;
        border-radius: 6px;
        padding: 5px 12px;
        font-size: 0.82rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        margin-bottom: 20px;
    }
    .id-badge i {
        font-size: 0.9rem;
        opacity: 0.7;
    }
</style>

<div class="container-fluid" style="padding: 0 15px;">
    <form class="announcement-form" method="POST" action="" autocomplete="nope">
        <input type="hidden" name="encodedby" id="encodedby" value="<?php echo $_SESSION['userid']; ?>">
        <input type="hidden" name="trans_type" id="trans_type" value="New">
        <input type="hidden" name="evacuee_id" id="evacuee_id" value="">
        <input type="hidden" name="announcement_id" id="announcement_id" value="">

        <div class="card announcement-card">
            <div class="card-header sticky-element bg-label-secondary d-flex justify-content-sm-start align-items-sm-center flex-column flex-sm-row" style="gap: 355px;">
                <h5 class="card-title mb-sm-0">ANNOUNCEMENT</h5>
                <h5 class="card-title mb-sm-0">LIST</h5>
            </div>

            <div class="card-body p-0">
                <div class="row g-0">

                    <!-- LEFT: New Announcement Form -->
                    <div class="col-12 col-lg-4 form-panel p-4">
                        <h6 class="text-muted text-uppercase fw-semibold mb-3" style="font-size: 0.75rem; letter-spacing: 0.05em;">New Announcement</h6>

                        <div class="mb-3">
                            <label class="form-label" for="ann_type">Type of Announcement <span class="text-danger">*</span></label>
                            <select id="ann_type" name="ann_type" class="form-select" required>
                                <option value="" selected>- select -</option>
                                <option value="General">General Announcement</option>
                                <option value="Event">Event</option>
                                <option value="Advisory">Advisory</option>
                                <option value="Memo">Memo</option>
                                <option value="Notice">Notice</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                            <input type="text" id="title" name="title" class="form-control" placeholder="Enter title" required />
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="ann_desc">Description <span class="text-danger">*</span></label>
                            <textarea id="ann_desc" name="ann_desc" class="form-control" rows="6" placeholder="Enter announcement description here..." required></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="btn_announcement_submit" class="btn btn-outline-success" id="btn-save">
                                <i class="ti tabler-device-floppy me-2"></i>Save Announcement
                            </button>
                        </div>

                        <div id="announcementError" class="alert alert-danger mt-3" style="display:none;"></div>
                    </div>

                    <!-- RIGHT: Table Panel -->
                    <div class="col-12 col-lg-8 p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div style="width: 260px;">
                                    <input type="text"
                                        id="searchAnnouncement"
                                        class="form-control form-control-sm"
                                        placeholder="🔍 Search by title..."
                                        autocomplete="off">
                                </div>
                            <div class="d-flex align-items-center gap-3">
                                <h6 class="text-muted text-uppercase fw-semibold mb-0" style="font-size: 0.75rem; letter-spacing: 0.05em;">Announcement Lists</h6>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="?route=announcement" class="btn btn-primary btn-sm">
                                    <i class="ti tabler-refresh me-1"></i>Refresh
                                </a>
                            </div>
                        </div>

                        <div class="table-responsive announcement-table-wrapper">
                            <table class="table table-hover table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Encoded By</th>
                                        <th>Date Created</th>
                                        <th>Edit</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>

<!-- ── Edit Drawer ── -->
<div class="edit-drawer-overlay" id="drawerOverlay"></div>

<div class="edit-drawer" id="editDrawer">
    <div class="edit-drawer-header">
        <span class="drawer-title">Edit Announcement</span>
        <button class="btn-close-drawer" id="btnCloseDrawer" title="Close">&#x2715;</button>
    </div>

    <div class="edit-drawer-body">
        <!-- ID Badge -->
        <div class="id-badge" id="drawerIdBadge">
            <i class="ti tabler-hash"></i>
            <span id="drawerIdText">—</span>
        </div>

        <div class="mb-3">
            <label class="form-label" for="edit_ann_type">Type of Announcement <span class="text-danger">*</span></label>
            <select id="edit_ann_type" class="form-select">
                <option value="" selected>- select -</option>
                <option value="General">General Announcement</option>
                <option value="Event">Event</option>
                <option value="Advisory">Advisory</option>
                <option value="Memo">Memo</option>
                <option value="Notice">Notice</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label" for="edit_title">Title <span class="text-danger">*</span></label>
            <input type="text" id="edit_title" class="form-control" placeholder="Enter title" />
        </div>

        <div class="mb-3">
            <label class="form-label" for="edit_ann_desc">Description <span class="text-danger">*</span></label>
            <textarea id="edit_ann_desc" class="form-control" rows="8" placeholder="Enter announcement description here..."></textarea>
        </div>
    </div>

    <div class="edit-drawer-footer">
        <button type="button" class="btn btn-success flex-fill" id="btnDrawerSave">
            <i class="ti tabler-device-floppy me-2"></i>Update Announcement
        </button>
        <button type="button" class="btn btn-label-secondary" id="btnDrawerCancel">Cancel</button>
    </div>
</div>