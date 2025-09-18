jQuery(document).ready(function($) {
    
    // Initialize date pickers
    $('.datepicker').datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true
    });
    
    // Quick export templates
    $('[data-template]').click(function(e) {
        e.preventDefault();
        
        var template = $(this).data('template');
        var today = new Date();
        var firstDay, lastDay;
        
        switch(template) {
            case 'this_month':
                firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                break;
                
            case 'last_month':
                firstDay = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                lastDay = new Date(today.getFullYear(), today.getMonth(), 0);
                break;
                
            case 'last_30_days':
                firstDay = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
                lastDay = today;
                break;
                
            case 'completed_only':
                // Just set status filter
                $('input[name="statuses[]"]').prop('checked', false);
                $('input[name="statuses[]"][value="wc-completed"]').prop('checked', true);
                return;
        }
        
        if (firstDay && lastDay) {
            $('#date_from').val(formatDate(firstDay));
            $('#date_to').val(formatDate(lastDay));
        }
        
        // Set default to completed orders
        if (template !== 'completed_only') {
            $('input[name="statuses[]"]').prop('checked', false);
            $('input[name="statuses[]"][value="wc-completed"]').prop('checked', true);
            $('input[name="statuses[]"][value="wc-processing"]').prop('checked', true);
        }
    });
    
    // Preview orders
    $('#kpe-preview').click(function(e) {
        e.preventDefault();

        var $button = $(this);
        var originalText = $button.text();

        $button.html('<span class="kpe-spinner"></span>' + kpe_ajax.strings.exporting).prop('disabled', true);

        var formData = $('#kpe-export-form').serialize();
        formData += '&action=kpe_preview_orders';

        $.post(kpe_ajax.ajax_url, formData, function(response) {
            if (response.success) {
                displayPreview(response.data);
            } else {
                showError(response.data || kpe_ajax.strings.export_error);
            }
        })
        .fail(function() {
            showError(kpe_ajax.strings.export_error);
        })
        .always(function() {
            $button.html(originalText).prop('disabled', false);
        });
    });
    
    // Export orders
    $('#kpe-export-form').submit(function(e) {
        e.preventDefault();

        var $button = $('#kpe-export');
        var originalText = $button.text();

        $button.html('<span class="kpe-spinner"></span>' + kpe_ajax.strings.exporting).prop('disabled', true);

        var formData = $(this).serialize();
        formData += '&action=kpe_export_orders';

        $.post(kpe_ajax.ajax_url, formData, function(response) {
            if (response.success) {
                displayExportResult(response.data);
            } else {
                showError(response.data || kpe_ajax.strings.export_error);
            }
        })
        .fail(function() {
            showError(kpe_ajax.strings.export_error);
        })
        .always(function() {
            $button.html(originalText).prop('disabled', false);
        });
    });
    
    // Display preview results
    function displayPreview(data) {
        var html = '<div class="kpe-preview-results">';
        
        if (data.data && data.data.length > 0) {
            html += '<div class="kpe-preview-summary">';
            html += '<p><strong>Preview Results:</strong> Showing ' + data.data.length + ' of ' + data.total_count + ' total orders</p>';
            html += '</div>';
            
            html += '<div class="kpe-preview-table-wrapper">';
            html += '<table class="wp-list-table widefat fixed striped">';
            
            // Headers
            html += '<thead><tr>';
            if (data.columns && data.columns.length > 0) {
                // Show first 8 columns to avoid horizontal scroll
                var displayColumns = data.columns.slice(0, 8);
                displayColumns.forEach(function(column) {
                    html += '<th>' + column.replace(/_/g, ' ') + '</th>';
                });
                if (data.columns.length > 8) {
                    html += '<th>... +' + (data.columns.length - 8) + ' more columns</th>';
                }
            }
            html += '</tr></thead>';
            
            // Data rows
            html += '<tbody>';
            data.data.forEach(function(row) {
                html += '<tr>';
                if (data.columns && data.columns.length > 0) {
                    var displayColumns = data.columns.slice(0, 8);
                    displayColumns.forEach(function(column) {
                        var value = row[column] || '';
                        if (typeof value === 'string' && value.length > 50) {
                            value = value.substring(0, 50) + '...';
                        }
                        html += '<td>' + escapeHtml(value) + '</td>';
                    });
                    if (data.columns.length > 8) {
                        html += '<td><em>... more data</em></td>';
                    }
                }
                html += '</tr>';
            });
            html += '</tbody>';
            
            html += '</table>';
            html += '</div>';
            
            if (data.total_count > data.data.length) {
                html += '<p class="description">This is a preview of the first ' + data.data.length + ' results. The full export will contain all ' + data.total_count + ' orders.</p>';
            }
        } else {
            html += '<div class="notice notice-warning"><p>' + kpe_ajax.strings.no_orders_found + '</p></div>';
        }
        
        html += '</div>';
        
        $('#kpe-results-content').html(html);
        $('#kpe-results').show();
        
        // Scroll to results
        $('html, body').animate({
            scrollTop: $('#kpe-results').offset().top - 100
        }, 500);
    }
    
    // Display export results
    function displayExportResult(data) {
        var html = '<div class="kpe-export-success">';
        html += '<div class="notice notice-success">';
        html += '<p><strong>' + kpe_ajax.strings.export_complete + '</strong><span class="kpe-success-badge">✓ Success</span></p>';
        html += '</div>';

        html += '<div class="kpe-export-details">';
        html += '<table class="form-table">';
        html += '<tr><th>📄 File:</th><td><code>' + escapeHtml(data.filename) + '</code></td></tr>';
        html += '<tr><th>📊 Records:</th><td><strong>' + data.count.toLocaleString() + '</strong> rows exported</td></tr>';
        html += '<tr><th>💾 File Size:</th><td>' + data.filesize + '</td></tr>';
        html += '<tr><th>⏰ Generated:</th><td>' + new Date().toLocaleString() + '</td></tr>';
        html += '</table>';
        html += '</div>';

        html += '<div class="kpe-download-section">';
        html += '<p><a href="' + data.download_url + '" class="button button-primary button-large">📥 Download CSV File</a></p>';
        html += '<p class="description">🔒 The file will be automatically deleted after download for security.</p>';
        html += '<p class="description">💡 <strong>Tip:</strong> Open the CSV file in Excel, Google Sheets, or any spreadsheet application.</p>';
        html += '</div>';

        html += '</div>';

        $('#kpe-results-content').html(html);
        $('#kpe-results').show().addClass('show');

        // Scroll to results
        $('html, body').animate({
            scrollTop: $('#kpe-results').offset().top - 100
        }, 500);
    }
    
    // Show error message
    function showError(message) {
        var html = '<div class="notice notice-error">';
        html += '<p><strong>Error:</strong> ' + escapeHtml(message) + '</p>';
        html += '</div>';
        
        $('#kpe-results-content').html(html);
        $('#kpe-results').show();
        
        // Scroll to results
        $('html, body').animate({
            scrollTop: $('#kpe-results').offset().top - 100
        }, 500);
    }
    
    // Utility functions
    function formatDate(date) {
        var year = date.getFullYear();
        var month = ('0' + (date.getMonth() + 1)).slice(-2);
        var day = ('0' + date.getDate()).slice(-2);
        return year + '-' + month + '-' + day;
    }
    
    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        
        return text.toString().replace(/[&<>"']/g, function(m) { 
            return map[m]; 
        });
    }
    
    // Form validation
    $('#kpe-export-form').on('submit', function(e) {
        var hasStatus = $('input[name="statuses[]"]:checked').length > 0;
        
        if (!hasStatus) {
            e.preventDefault();
            showError('Please select at least one order status to export.');
            return false;
        }
        
        // Validate date range
        var dateFrom = $('#date_from').val();
        var dateTo = $('#date_to').val();
        
        if (dateFrom && dateTo && dateFrom > dateTo) {
            e.preventDefault();
            showError('Start date cannot be later than end date.');
            return false;
        }
        
        return true;
    });
    
    // Auto-update export format based on selections
    $('input[name="include_billing"], input[name="include_shipping"], input[name="include_items"]').change(function() {
        var includeBilling = $('input[name="include_billing"]').is(':checked');
        var includeShipping = $('input[name="include_shipping"]').is(':checked');
        var includeItems = $('input[name="include_items"]').is(':checked');
        
        if (!includeBilling && !includeShipping && includeItems) {
            $('#format').val('items_only');
        } else if ((includeBilling || includeShipping) && !includeItems) {
            $('#format').val('summary');
        } else {
            $('#format').val('detailed');
        }
    });
    
    // Show/hide additional options based on format
    $('#format').change(function() {
        var format = $(this).val();

        switch(format) {
            case 'summary':
                $('input[name="include_billing"]').prop('checked', true);
                $('input[name="include_shipping"]').prop('checked', true);
                $('input[name="include_items"]').prop('checked', false);
                break;
                
            case 'items_only':
                $('input[name="include_billing"]').prop('checked', false);
                $('input[name="include_shipping"]').prop('checked', false);
                $('input[name="include_items"]').prop('checked', true);
                break;
                
            case 'detailed':
            default:
                $('input[name="include_billing"]').prop('checked', true);
                $('input[name="include_shipping"]').prop('checked', true);
                $('input[name="include_items"]').prop('checked', true);
                break;
        }
    });
});