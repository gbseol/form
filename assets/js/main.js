/**
 * ============================================================================
 * Computer Lab Management System - Main JavaScript
 * ============================================================================
 * - Sidebar toggle for small screens
 * - Enhanced confirm dialogs for destructive actions
 * - Dashboard chart rendering (via AJAX / dashboard_ajax.php)
 * - Auto-dismissing alerts
 * ============================================================================
 */

(function () {
    'use strict';

    // ------------------------------------------------------------------------
    // Sidebar (mobile) toggle
    // ------------------------------------------------------------------------
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('appSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');

    if (sidebarToggle && sidebar && backdrop) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
            backdrop.classList.toggle('show');
        });
        backdrop.addEventListener('click', function () {
            sidebar.classList.remove('open');
            backdrop.classList.remove('show');
        });
    }

    // ------------------------------------------------------------------------
    // Forms: replace native confirm() with a styled Bootstrap modal
    // ------------------------------------------------------------------------
    // Any <form> with data-confirm="Message" triggers the modal on submit.
    // ------------------------------------------------------------------------
    let confirmForm = null;
    const modalEl = document.getElementById('confirmModal');

    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            confirmForm = form;
            const msg = form.getAttribute('data-confirm');
            document.getElementById('confirmModalMessage').textContent = msg;
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        });
    });

    if (modalEl) {
        const confirmBtn = document.getElementById('confirmModalOk');
        confirmBtn.addEventListener('click', function () {
            if (confirmForm) {
                bootstrap.Modal.getInstance(modalEl).hide();
                confirmForm.removeAttribute('data-confirm'); // allow native submit
                confirmForm.submit();
            }
        });
    }

    // ------------------------------------------------------------------------
    // Auto-dismiss alert messages after 6 seconds
    // ------------------------------------------------------------------------
    document.querySelectorAll('.alert-dismissible').forEach(function (alert) {
        setTimeout(function () {
            const instance = bootstrap.Alert.getOrCreateInstance(alert);
            if (instance) {
                instance.close();
            }
        }, 6000);
    });

    // ------------------------------------------------------------------------
    // Profile photo: live preview before saving
    // ------------------------------------------------------------------------
    // Any file input with data-preview="#imgSelector" (and optionally
    // data-fallback="#iconSelector") shows the chosen image immediately.
    // ------------------------------------------------------------------------
    document.querySelectorAll('input[type="file"][data-preview]').forEach(function (input) {
        const img = document.querySelector(input.getAttribute('data-preview'));
        const fallback = input.getAttribute('data-fallback')
            ? document.querySelector(input.getAttribute('data-fallback'))
            : null;
        if (!img) {
            return;
        }

        const originalSrc = img.getAttribute('src') || '';

        input.addEventListener('change', function () {
            const file = input.files && input.files[0];

            if (!file) {
                // Restore whatever was there before (keep or hide preview).
                if (originalSrc) {
                    img.src = originalSrc;
                    img.classList.remove('d-none');
                    if (fallback) fallback.classList.add('d-none');
                } else {
                    img.classList.add('d-none');
                    if (fallback) fallback.classList.remove('d-none');
                }
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                img.src = e.target.result;
                img.classList.remove('d-none');
                if (fallback) fallback.classList.add('d-none');
            };
            reader.readAsDataURL(file);
        });
    });

    // ------------------------------------------------------------------------
    // Add Computer: auto-suggest the Computer ID when a room is chosen
    // ------------------------------------------------------------------------
    const labSelect = document.getElementById('labSelect');
    const computerIdField = document.getElementById('computerIdField');

    if (labSelect && computerIdField) {
        const suggestId = function () {
            const labId = labSelect.value;
            if (!labId) {
                computerIdField.value = '';
                return;
            }
            fetch('next_id.php?lab_id=' + encodeURIComponent(labId), {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.ok) {
                    computerIdField.value = data.computer_id;
                }
            })
            .catch(function () { /* keep whatever is typed if the request fails */ });
        };

        labSelect.addEventListener('change', suggestId);
        // Pre-fill on load when a room is already selected (e.g. after an error).
        if (labSelect.value) {
            suggestId();
        }
    }

    // ------------------------------------------------------------------------
    // Add Computer: issue description becomes required when not "Working"
    // ------------------------------------------------------------------------
    const computerStatus = document.getElementById('computerStatus');
    const computerRemarks = document.getElementById('computerRemarks');

    if (computerStatus && computerRemarks) {
        const toggleIssueRequired = function () {
            const needsDescription = computerStatus.value !== 'Working';
            computerRemarks.required = needsDescription;
        };
        computerStatus.addEventListener('change', toggleIssueRequired);
        toggleIssueRequired();
    }

    // ------------------------------------------------------------------------
    // Quick status change from the computer inventory table (AJAX)
    // ------------------------------------------------------------------------
    document.querySelectorAll('.status-quick').forEach(function (select) {
        select.addEventListener('change', function () {
            const url = this.getAttribute('data-url');
            const id = this.getAttribute('data-id');
            const status = this.value;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const body = new URLSearchParams();
            body.append('computer_id', id);
            body.append('status', status);
            body.append('csrf_token', token);

            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                credentials: 'same-origin',
                body: body.toString()
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.ok) {
                    // Reflect the change visually.
                    select.closest('td').innerHTML = '<span class="badge bg-success">' + status + '</span>';
                } else {
                    alert(data.error || 'Failed to update status.');
                    select.value = select.options[0].value;
                }
            })
            .catch(function () {
                alert('Network error while updating status.');
            });
        });
    });

    // ------------------------------------------------------------------------
    // Light / Dark theme toggle
    // ------------------------------------------------------------------------
    // One button in the top bar. The preference is applied immediately to the
    // <html> element (Bootstrap 5.3 dark mode + custom .dark-mode overrides),
    // remembered in localStorage for this browser and persisted per user via
    // theme_ajax.php so it follows them on other devices.
    // ------------------------------------------------------------------------
    const themeToggle = document.getElementById('themeToggle');

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            const root = document.documentElement;
            const isDark = root.classList.contains('dark-mode');
            const theme = isDark ? 'light' : 'dark';

            root.classList.toggle('dark-mode', !isDark);
            root.setAttribute('data-bs-theme', theme);

            try {
                localStorage.setItem('clms_theme', theme);
            } catch (err) { /* storage unavailable - keep DB preference only */ }

            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const body = new URLSearchParams();
            body.append('theme', theme);
            body.append('csrf_token', token);

            fetch('theme_ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                credentials: 'same-origin',
                body: body.toString()
            }).catch(function () {
                // The local change still applies; the server sync will retry
                // the next time the user toggles.
            });
        });
    }

    // ------------------------------------------------------------------------
    // Dashboard: "Report an Issue" picker
    // ------------------------------------------------------------------------
    // Clicking the box reveals the lab list. Choosing a lab shows the computers
    // in that lab. Choosing a computer sends the user to its edit page with
    // the issue form highlighted.
    // ------------------------------------------------------------------------
    const issuePickerToggle = document.getElementById('issuePickerToggle');
    const issuePicker = document.getElementById('issuePicker');
    const issueLabSelect = document.getElementById('issueLabSelect');
    const issuePcSelect = document.getElementById('issuePcSelect');
    const issueGoBtn = document.getElementById('issueGoBtn');

    if (issuePickerToggle && issuePicker) {
        let issueData = { labs: [] };

        const loadLabs = function () {
            fetch('issues/lab_pcs.php', {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                issueData = data;
                issueLabSelect.innerHTML = '<option value="">-- Select Lab --</option>';
                (data.labs || []).forEach(function (lab) {
                    const opt = document.createElement('option');
                    opt.value = lab.id;
                    opt.textContent = lab.name + ' (' + lab.computers.length + ' PC)';
                    issueLabSelect.appendChild(opt);
                });
            })
            .catch(function () {
                issueLabSelect.innerHTML = '<option value="">Could not load labs</option>';
            });
        };

        issuePickerToggle.addEventListener('click', function () {
            const hidden = issuePicker.classList.toggle('d-none');
            if (!hidden && issueLabSelect.options.length <= 1) {
                loadLabs();
            }
        });

        issueLabSelect.addEventListener('change', function () {
            const lab = issueData.labs.find(function (l) {
                return String(l.id) === String(issueLabSelect.value);
            });
            issuePcSelect.innerHTML = '<option value="">-- Select Computer --</option>';
            issuePcSelect.disabled = !lab;
            issueGoBtn.disabled = true;
            if (lab) {
                lab.computers.forEach(function (pc) {
                    const opt = document.createElement('option');
                    opt.value = pc.id;
                    opt.textContent = pc.computer_id + ' (' + pc.status + ')';
                    issuePcSelect.appendChild(opt);
                });
            }
        });

        issuePcSelect.addEventListener('change', function () {
            issueGoBtn.disabled = !issuePcSelect.value;
        });

        issueGoBtn.addEventListener('click', function () {
            if (issuePcSelect.value) {
                window.location.href = 'computers/edit.php?id=' + encodeURIComponent(issuePcSelect.value) + '&report=1';
            }
        });
    }

    // ------------------------------------------------------------------------
    // Edit computer page reached from the issue picker (?report=1):
    // lightly highlight the "Report an Issue" section without scrolling
    // (staff should see the whole page from the top, not be dragged down).
    // ------------------------------------------------------------------------
    if (new URLSearchParams(window.location.search).get('report') === '1') {
        const issueReportCard = document.getElementById('issueReportCard');
        if (issueReportCard) {
            issueReportCard.classList.add('border', 'border-warning', 'shadow-sm');
        }
    }

    // ------------------------------------------------------------------------
    // Dashboard charts - loads data from dashboard_ajax.php via AJAX
    // ------------------------------------------------------------------------
    const chartArea = document.getElementById('dashboardCharts');
    if (chartArea) {
        fetch('dashboard_ajax.php', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
        .then(function (res) { return res.json(); })
        .then(function (data) { renderDashboardCharts(data); })
        .catch(function () { /* charts simply do not render if the call fails */ });
    }

    /**
     * Render the three dashboard charts using Chart.js.
     * @param {Object} data
     */
    function renderDashboardCharts(data) {
        const themeColor = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim() || '#0d6efd';

        // 1. Computers per Lab (bar)
        const labCanvas = document.getElementById('chartLabs');
        if (labCanvas && Array.isArray(data.labs)) {
            new Chart(labCanvas, {
                type: 'bar',
                data: {
                    labels: data.labs.map(function (l) { return l.name; }),
                    datasets: [{
                        label: 'Computers',
                        data: data.labs.map(function (l) { return l.total; }),
                        backgroundColor: 'rgba(59,130,246,.7)',
                        borderColor: themeColor,
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        }

        // 2. Working vs Faulty (doughnut)
        const statusCanvas = document.getElementById('chartStatus');
        if (statusCanvas && Array.isArray(data.status)) {
            new Chart(statusCanvas, {
                type: 'doughnut',
                data: {
                    labels: data.status.map(function (s) { return s.status; }),
                    datasets: [{
                        data: data.status.map(function (s) { return s.total; }),
                        backgroundColor: [
                            '#22c55e', // Working
                            '#ef4444', // Faulty
                            '#f59e0b', // Under Maintenance
                            '#64748b', // Out of Service
                            '#1e293b'  // Scrapped
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

        // 2b. Issues (staff) - Open vs Solved (doughnut)
        const issuesCanvas = document.getElementById('chartIssues');
        if (issuesCanvas && Array.isArray(data.issues)) {
            const openTotal   = (data.issues[0] || {}).total || 0;
            const solvedTotal = (data.issues[1] || {}).total || 0;
            const realTotals  = { 'Open Issues': openTotal, 'Solved Issues': solvedTotal };

            // When nothing is reported yet, draw a single full green circle.
            const hasIssues = (openTotal + solvedTotal) > 0;
            const slices = hasIssues
                ? data.issues
                : [{ label: 'Solved Issues', total: 1 }];
            const colors = hasIssues ? ['#f59e0b', '#22c55e'] : ['#22c55e'];

            new Chart(issuesCanvas, {
                type: 'doughnut',
                data: {
                    labels: slices.map(function (s) { return s.label; }),
                    datasets: [{
                        data: slices.map(function (s) { return s.total; }),
                        backgroundColor: colors,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    const n = realTotals[ctx.label] !== undefined ? realTotals[ctx.label] : 0;
                                    return ' ' + ctx.label + ': ' + n;
                                }
                            }
                        }
                    }
                }
            });
        }

        // 3. Monthly updates (line)
        const monthlyCanvas = document.getElementById('chartMonthly');
        if (monthlyCanvas && Array.isArray(data.monthly)) {
            new Chart(monthlyCanvas, {
                type: 'line',
                data: {
                    labels: data.monthly.map(function (m) { return m.label; }),
                    datasets: [{
                        label: 'Computers Added',
                        data: data.monthly.map(function (m) { return m.total; }),
                        borderColor: themeColor,
                        backgroundColor: 'rgba(59,130,246,.15)',
                        fill: true,
                        tension: .35,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        }
    }
})();
