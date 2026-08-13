@extends('layouts.main')
@section('title', 'Organization Role')

@section('content')

<div class="modal fade" id="saveModal" tabindex="-1" data-bs-backdrop="static" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Save Permissions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">Are you sure you want to save permissions for this user?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmSave">Yes, Save</button>
            </div>
        </div>
    </div>
</div>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card-header border-bottom">
        <h4 class="mb-0">Organization Roles</h4>
    </div>

    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="mb-0">Organization Role Permissions</h5>
        </div>
        <div class="card-body pt-3">

            <div class="row g-3 align-items-end mb-4">

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Organization</label>
                    <select id="organization_id" class="form-select">
                        <option value="">-- Select Organization --</option>
                        @foreach($organizations as $org)
                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Role</label>
                    <select id="role_id" class="form-select">
                        <option value="">-- Select Role --</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Loading spinner (hidden by default) --}}
                <div class="col-md-1 d-flex align-items-end">
                    <div id="loadingSpinner" style="display:none; padding-bottom:6px;">
                        <span class="spinner-border spinner-border-sm text-primary"></span>
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">User</label>
                    <select id="user_id" class="form-select" disabled>
                        <option value="">-- Select User --</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button id="btn-view" class="btn btn-success w-100" disabled>
                        <i class="bx bx-show me-1"></i> View
                    </button>
                </div>

            </div>

            <div id="noMsg" class="text-danger mb-3" style="display:none;">
                No users found for the selected organization and role.
            </div>

            <div id="permissionTableWrap" style="display:none;">
                <div class="mb-3">
                    <span class="fw-semibold">Permissions for: </span>
                    <span id="selected-user-name" class="text-primary fw-bold"></span>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px;">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </th>
                                <th>Sidebar Name</th>
                                <th class="text-center">Add</th>
                                <th class="text-center">Edit</th>
                                <th class="text-center">View</th>
                                <th class="text-center">Delete</th>
                            </tr>
                        </thead>
                        <tbody id="permissionBody"></tbody>
                    </table>
                </div>
                
                @can('edit.organization-role')
                <div class="mt-3">
                    <button type="button" class="btn btn-primary" id="savePermissionsBtn">
                        <i class="bx bx-save me-1"></i> Save Permissions
                    </button>
                </div>
                @endcan
            </div>

        </div>
    </div>
</div>

@endsection

@section('main-scripts')
<script>
    $(document).ready(function() {

        var allPermissions = @json($permissions);

        var modules = [
            { label: 'Favicon',      key: 'favicon'      },
            { label: 'Role',         key: 'role'         },
            { label: 'Home Tab',     key: 'hometab'      },
            { label: 'Store',        key: 'store'        },
            { label: 'Category',     key: 'category'     },
            { label: 'Brand',        key: 'brand'        },
            { label: 'Item',         key: 'item'         },
            { label: 'Users',        key: 'user'         },
            { label: 'Driver List',  key: 'driverlist'   },
            { label: 'Inventory',    key: 'inventory'    },
            { label: 'Vendor',       key: 'vendor'       },
            { label: 'Retailer',     key: 'retailer'     },
            { label: 'Wholesaler',   key: 'wholesaler'   },
            { label: 'Discount',     key: 'discount'     },
            { label: 'Coupon',       key: 'coupon'       },
            { label: 'Loyalty',      key: 'loyalty'      },
            { label: 'Order',        key: 'order'        },
            { label: 'Invoice',      key: 'invoice'      },
            { label: 'Refund',       key: 'refund'       },
            { label: 'Report',       key: 'report'       },
            { label: 'Heatmap',      key: 'heatmap'      },
            { label: 'Notification', key: 'notification' },
        ];

        // ── Auto-load users when BOTH org and role are selected ──────────
        function tryLoadUsers() {
            var orgId  = $('#organization_id').val();
            var roleId = $('#role_id').val();

            // Reset state
            $('#permissionTableWrap').hide();
            $('#noMsg').hide();
            $('#user_id').prop('disabled', true).html('<option value="">-- Select User --</option>');
            $('#btn-view').prop('disabled', true);

            if (!orgId || !roleId) return; // wait until both are chosen

            $('#loadingSpinner').show();

            $.ajax({
                url: '{{ route("organization.role.users") }}',
                type: 'POST',
                data: {
                    organization_id: orgId,
                    role_id: roleId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    var $sel = $('#user_id').html('<option value="">-- Select User --</option>');
                    if (res.success && res.users.length > 0) {
                        $.each(res.users, function(i, u) {
                            $sel.append('<option value="' + u.id + '">' + u.name + ' (' + (u.email || '') + ')</option>');
                        });
                        $sel.prop('disabled', false);
                    } else {
                        $sel.prop('disabled', true);
                        $('#noMsg').show();
                    }
                },
                error: function(xhr) {
                    alert('Error: ' + (xhr.responseJSON ? xhr.responseJSON.message : xhr.responseText));
                },
                complete: function() {
                    $('#loadingSpinner').hide();
                }
            });
        }

        // Trigger auto-load on either dropdown change
        $('#organization_id, #role_id').on('change', function() {
            tryLoadUsers();
        });

        // ── Enable View when user selected ──────────────────────────────
        $('#user_id').on('change', function() {
            $('#btn-view').prop('disabled', !$(this).val());
            $('#permissionTableWrap').hide();
        });

        // ── View permissions ─────────────────────────────────────────────
        $('#btn-view').on('click', function() {
            var userId = $('#user_id').val();
            if (!userId) return;

            var btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Loading...');

            $.ajax({
                url: '{{ route("organization.role.user-permissions") }}',
                type: 'POST',
                data: {
                    user_id: userId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    if (res.success) {
                        $('#selected-user-name').text(res.user_name);
                        buildMatrix(res.permissions);
                        $('#permissionTableWrap').show();
                    }
                },
                error: function() {
                    alert('Error loading permissions.');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="bx bx-show me-1"></i> View');
                }
            });
        });

        // ── Build matrix ─────────────────────────────────────────────────
        function buildMatrix(userPermissionIds) {
            var $tbody = $('#permissionBody').empty();

            $.each(modules, function(i, mod) {
                var row = '<tr>';
                row += '<td><input type="checkbox" class="form-check-input row-select" data-key="' + mod.key + '"></td>';
                row += '<td>' + mod.label + '</td>';

                $.each(['add', 'edit', 'view', 'delete'], function(j, action) {
                    var permName = mod.key + '.' + action;
                    var perm = null;
                    $.each(allPermissions, function(k, p) {
                        if (p.name === permName) { perm = p; return false; }
                    });
                    var permId  = perm ? perm.id : null;
                    var checked = (perm && userPermissionIds.indexOf(perm.id) !== -1) ? 'checked' : '';
                    row += '<td class="text-center"><input type="checkbox" class="form-check-input perm-check" data-perm-id="' + permId + '" data-key="' + mod.key + '" data-action="' + action + '" ' + checked + '></td>';
                });

                row += '</tr>';
                $tbody.append(row);
            });

            $.each(modules, function(i, mod) {
                var $checks = $('.perm-check[data-key="' + mod.key + '"]');
                var allChk  = $checks.length > 0 && $checks.not(':checked').length === 0;
                $('.row-select[data-key="' + mod.key + '"]').prop('checked', allChk);
            });
        }

        $('#selectAll').on('change', function() {
            $('.perm-check, .row-select').prop('checked', $(this).is(':checked'));
        });

        $(document).on('change', '.row-select', function() {
            $('.perm-check[data-key="' + $(this).data('key') + '"]').prop('checked', $(this).is(':checked'));
        });

        $('#savePermissionsBtn').on('click', function() {
            new bootstrap.Modal(document.getElementById('saveModal')).show();
        });

        $('#confirmSave').on('click', function() {
            var userId    = $('#user_id').val();
            var permNames = [];
            $('.perm-check:checked').each(function() {
                permNames.push($(this).data('key') + '.' + $(this).data('action'));
            });

            $.ajax({
                url: '{{ route("organization.role.save-permissions") }}',
                type: 'POST',
                data: {
                    user_id: userId,
                    perm_names: permNames,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    bootstrap.Modal.getInstance(document.getElementById('saveModal')).hide();
                    Swal.fire(res.success ? 'Success' : 'Error', res.message || 'Done', res.success ? 'success' : 'error');
                },
                error: function() {
                    Swal.fire('Error', 'Something went wrong!', 'error');
                }
            });
        });

    });
</script>
@endsection