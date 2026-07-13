function generate_accounting_si(raw_data) {
    function summarizeInvoices(rawData) {
        const data = typeof rawData === "string" ? JSON.parse(rawData) : rawData;
        if (!Array.isArray(data)) throw new Error("Input must be an array or JSON array string");

        const toNum = v => {
            if (v === null || v === undefined || v === "") return 0;
            const n = Number(v);
            return Number.isFinite(n) ? n : 0;
        };

        // Step A: merge by siid + siilid
        const lineMap = new Map();
        for (const row of data) {
            const key = `${row.siid}||${row.siilid}`;
            if (!lineMap.has(key)) lineMap.set(key, []);
            lineMap.get(key).push(row);
        }

        // Compute merged lines with numeric totals
        const mergedLines = []; // { siid, siilid, client_..., price, qty, combinedDiscountPerItem, lineTotal, si_paid, si_status, updated_at }
        for (const [key, group] of lineMap.entries()) {
            const first = group[0];
            const allSamePrice = group.every(g => String(g.si_item_price) === String(first.si_item_price));
            const allSameQty = group.every(g => String(g.si_item_qty) === String(first.si_item_qty));

            const sumDiscount = group.reduce((s, g) => s + toNum(g.discount), 0);

            if (allSamePrice && allSameQty) {
            const price = toNum(first.si_item_price);
            const qty = toNum(first.si_item_qty);
            const discountPerItem = sumDiscount; // sum of per-entry discounts becomes per-item discount
            const lineTotal = Math.round(price * qty - discountPerItem * qty);
            mergedLines.push({
                siid: first.siid,
                siilid: first.siilid,
                client_name: first.client_name,
                si_date: first.si_date,
                updated_at: first.updated_at,
                client_id: first.client_id,
                client_client_id: first.client_client_id,
                si_item_price: String(price),
                si_item_qty: String(qty),
                combined_discount_per_item: discountPerItem,
                lineTotal,
                freight_cost: first.freight_cost,
                si_paid: first.si_paid,
                si_status: first.si_status
            });
            } else {
            // Mixed price/qty: sum each entry's total individually
            let aggregatedTotal = 0;
            let aggregatedQty = 0;
            for (const g of group) {
                const price = toNum(g.si_item_price);
                const qty = toNum(g.si_item_qty);
                const discountPerItem = toNum(g.discount);
                aggregatedTotal += Math.round(price * qty - discountPerItem * qty);
                aggregatedQty += qty;
            }
            mergedLines.push({
                siid: first.siid,
                siilid: first.siilid,
                client_name: first.client_name,
                si_date: first.si_date,
                updated_at: first.updated_at,
                client_id: first.client_id,
                client_client_id: first.client_client_id,
                si_item_price: group[0].si_item_price, // keep representative
                si_item_qty: String(aggregatedQty),
                combined_discount_per_item: sumDiscount,
                lineTotal: aggregatedTotal,
                freight_cost: first.freight_cost,
                si_paid: first.si_paid,
                si_status: first.si_status
            });
            }
        }

        // Step B: group merged lines by invoice (siid) and compute invoice totals
        const invoiceMap = new Map();
        for (const line of mergedLines) {
            if (!invoiceMap.has(line.siid)) {
            invoiceMap.set(line.siid, {
                siid: line.siid,
                client_name: line.client_name,
                si_date: line.si_date,
                updated_at: line.updated_at,
                client_id: line.client_id,
                client_client_id: line.client_client_id,
                si_paid: line.si_paid,
                si_status: line.si_status,
                freight_cost: line.freight_cost,
                _total: 0
            });
            }
            const inv = invoiceMap.get(line.siid);

            // Keep the latest updated_at
            if (line.updated_at && inv.updated_at) {
            if (new Date(line.updated_at) > new Date(inv.updated_at)) inv.updated_at = line.updated_at;
            } else if (line.updated_at) {
            inv.updated_at = line.updated_at;
            }

            inv._total += toNum(line.lineTotal);
        }

        // Build final array (one object per invoice) with numeric fields
        const invoices = [];
        for (const inv of invoiceMap.values()) {
            const siIdNum = Number(inv.siid);
            const siPaidNum = Number(inv.si_paid);
            const totalAmtNum = Math.round((Number(inv._total) || 0) + toNum(inv.freight_cost));

            invoices.push({
                // Provide both for compatibility: numeric si_id and legacy siid
                si_id: Number.isFinite(siIdNum) ? siIdNum : null,
                siid: Number.isFinite(siIdNum) ? siIdNum : null,
                client_name: inv.client_name,
                si_date: inv.si_date,
                updated_at: inv.updated_at,
                client_id: inv.client_id,
                client_client_id: inv.client_client_id,
                total_amount: totalAmtNum,
                si_paid: Number.isFinite(siPaidNum) ? siPaidNum : 0,
                si_status: inv.si_status
            });
        }

        return invoices;
    }

    // Export for Node usage (optional)
    if (typeof module !== "undefined" && module.exports) {
        module.exports = summarizeInvoices;
    }
    const discountSummary = summarizeInvoices(raw_data.summary);

    const data = {
        items: raw_data.items || [],
        summary: discountSummary
    };

    // Normalize item fields to numbers for consistent downstream processing
    try {
        if (Array.isArray(data.items)) {
            data.items = data.items.map(function (it) {
                return Object.assign({}, it, {
                    discount: (it && it.discount != null && it.discount !== '') ? Number(it.discount) : 0,
                    price: (it && it.price != null && it.price !== '') ? Number(it.price) : 0,
                    product_id: (it && it.product_id != null && it.product_id !== '') ? Number(it.product_id) : 0,
                    qty: (it && it.qty != null && it.qty !== '') ? Number(it.qty) : 0,
                    si_disc_id: (it && it.si_disc_id != null && it.si_disc_id !== '') ? Number(it.si_disc_id) : 0,
                    si_id: (it && it.si_id != null && it.si_id !== '') ? Number(it.si_id) : 0,
                    siil_id: (it && it.siil_id != null && it.siil_id !== '') ? Number(it.siil_id) : 0,
                    vat_switch: (it && it.vat_switch != null && it.vat_switch !== '') ? Number(it.vat_switch) : 0
                });
            });
        }
    } catch (e) {
        console.warn('generate_accounting_si: item normalization failed', e);
    }

    function escapeHtml(str) {
        return String(str).replace(/[&<>\"]/g, function (s) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'})[s];
        });
    }

    if (!data || !Array.isArray(data.items)) {
        console.warn('generate_accounting_si: invalid input, expected data.items array');
        return [];
    }   

        /* sticky header and frozen columns will be applied after DataTable init */

    // Build unique product groups by product_unique_id (fallback to product_id) with has_discount flag
    const getGroupKey = (it) => {
        if (it && it.product_unique_id) return String(it.product_unique_id);
        // Fallback to a namespaced product_id key to avoid clashing with non-numeric regex later
        return 'pid_' + String(it && it.product_id != null ? it.product_id : '');
    };

    const seen = new Map();
    for (const item of data.items) {
        if (!item) continue;
        const pid = item.product_id;
        if (pid == null) continue;

        const gkey = getGroupKey(item);
        const itemHasDiscount = (typeof item.discount === 'number' && item.discount > 0) || Boolean(item.si_disc_id);

        if (!seen.has(gkey)) {
            // Initialize group, use current item as representative
            seen.set(gkey, {
                group_key: gkey,
                product_id: item.product_id, // representative product_id (highest wins)
                product_code: item.product_code,
                product_name: item.product_name,
                has_discount: itemHasDiscount
            });
        } else {
            const existing = seen.get(gkey);
            // OR the discount flag
            if (!existing.has_discount && itemHasDiscount) existing.has_discount = true;
            // Keep the item with the higher product_id as the representative for name/code/etc
            if (Number(item.product_id) > Number(existing.product_id)) {
                existing.product_id = item.product_id;
                existing.product_code = item.product_code;
                existing.product_name = item.product_name;
            }
        }
    }

    const uniqueProducts = Array.from(seen.values());

    // build headers
    let headerHtml = '';
    for (const p of uniqueProducts) {
        headerHtml += '<th>QTY</th>';
        headerHtml += '<th>' + escapeHtml(String(p.product_code || '')).replace(/ /g, '&nbsp;') + '</th>';
        if (p.has_discount) headerHtml += '<th>Discount</th>';
    }

    // footer placeholders: five base columns then per-product columns
    let footerCells = '<th></th><th></th><th></th><th></th><th></th>';
    for (const p of uniqueProducts) {
        footerCells += '<th></th>'; // qty
        footerCells += '<th></th>'; // price/sales
        if (p.has_discount) footerCells += '<th></th>'; // discount
    }

    // small inline styles to draw a frame around each product group: left border on qty, right border on discount
    const tableStyles = '<style>' +
        '#si_table_accounting th[class*="product-group-start-"] , #si_table_accounting td[class*="product-group-start-"] { border-left:2px solid #666; }' +
        '#si_table_accounting th[class*="product-group-end-"] , #si_table_accounting td[class*="product-group-end-"] { border-right:2px solid #666; }' +
        '#si_table_accounting thead th { border-bottom:2px solid #ddd; }' +
        '</style>';

    const si_table_set = tableStyles + '<table id="si_table_accounting" class="table display nowrap" style="width:100%">' +
        '<thead><tr>' +
        '<th>Customer</th><th>Sales&nbsp;Invoice</th><th>Delivery&nbsp;Date</th><th>Total&nbsp;Amount</th><th>Payment&nbsp;Status</th>' +
        headerHtml +
        '</tr></thead>' +
        '<tfoot><tr>' + footerCells + '</tr></tfoot>' +
        '</table>';

    const container = $('#si_dr_table');
    if (container) {
        container.empty();
        container.append(si_table_set);
    }

    // Build DataTable columns
    const columns = [
        { data: 'client_name' },
        { data: 'si_id', render: function (data, type, row) {
            if (type === 'display' && row.si_status === 'cancelled') {
                return escapeHtml(String(data)) + ' <span>(cancelled)</span>';
            }
            return data;
        }},
        { data: 'si_date' },
        { data: 'total_amount', render: function (data) {
                const num = Number(data);
                if (!isFinite(num)) return '';
                if (typeof formatPrice === 'function') return formatPrice(num);
                return num.toFixed(2);
            }
        },
        { data: 'si_paid', render: function (data) {
                const val = (typeof data === 'string') ? data.trim() : data;
                return (val === 1 || val === '1') ? 'Paid' : 'Unpaid';
            }
        }
    ];

    for (const p of uniqueProducts) {
        const pid = p.product_id;

        columns.push({ data: 'qty_' + pid, className: 'product-group-start-' + pid, render: function (data) {
            if (Array.isArray(data)) return data.map(d => escapeHtml(String(d))).join('<br>');
            if (data === '' || data === null || typeof data === 'undefined') return '';
            return escapeHtml(String(data));
        }});

        columns.push({ data: 'price_' + pid, render: function (data) {
            if (Array.isArray(data)) {
                return data.map(n => {
                    if (n === '' || n === null || typeof n === 'undefined') return '';
                    const num = Number(n);
                    if (!isFinite(num)) return '';
                    if (typeof formatPrice === 'function') return formatPrice(num);
                    return num.toFixed(2);
                }).join('<br>');
            }
            if (data === '' || data === null || typeof data === 'undefined') return '';
            const num = Number(data);
            if (!isFinite(num)) return '';
            if (typeof formatPrice === 'function') return formatPrice(num);
            return num.toFixed(2);
        }});

        if (p.has_discount) {
            columns.push({ data: 'disc_' + pid, className: 'product-group-end-' + pid, render: function (data) {
                if (Array.isArray(data)) {
                    return data.map(n => {
                        if (n === '' || n === null || typeof n === 'undefined') return '';
                        const num = Number(n);
                        if (!isFinite(num)) return '';
                        if (typeof formatPrice === 'function') return formatPrice(num);
                        return num.toFixed(2);
                    }).join('<br>');
                }
                if (data === '' || data === null || typeof data === 'undefined') return '';
                const num = Number(data);
                if (!isFinite(num)) return '';
                if (typeof formatPrice === 'function') return formatPrice(num);
                return num.toFixed(2);
            }});
        }
    }

    // totalsMap: initialize before building tableData
    const totalsMap = {};
    for (const p of uniqueProducts) totalsMap[p.product_id] = { qty: 0, sales: 0 };

    // build tableData from summary rows
    const tableData = (Array.isArray(data.summary) ? data.summary : []).map(si => {
        const row = Object.assign({}, si);

        for (const p of uniqueProducts) {
            const pid = p.product_id;
            // Match by group key (product_unique_id if available, else product_id fallback)
            const matches = (Array.isArray(data.items) ? data.items : []).filter(it => {
                if (!it) return false;
                if (it.si_id !== si.si_id) return false;
                return getGroupKey(it) === p.group_key;
            });

            if (!matches || matches.length === 0) {
                row['qty_' + pid] = '';
                row['price_' + pid] = '';
                if (p.has_discount) row['disc_' + pid] = '';
                continue;
            }

            if (matches.length === 1) {
                const m = matches[0];
                const qtyVal = (m.qty !== undefined && m.qty !== null) ? m.qty : '';
                const priceVal = (typeof m.price !== 'undefined') ? m.price : '';

                row['qty_' + pid] = qtyVal;
                row['price_' + pid] = (priceVal === '' || priceVal === null || typeof priceVal === 'undefined') ? '' : priceVal;

                const qtyNum = Number(qtyVal) || 0;
                const priceNum = Number(priceVal) || 0;

                if (p.has_discount) {
                    // Show per-unit discount in the table cell, but subtract total discount from totalsMap.sales
                    const dPerUnit = (typeof m.discount === 'number') ? m.discount : (Number(m.discount) || 0);
                    const discTotal = dPerUnit * qtyNum;
                    // show per-unit discount (not multiplied by qty) in the cell
                    row['disc_' + pid] = dPerUnit ? dPerUnit : '';

                    if (si.si_status !== 'cancelled') {
                        totalsMap[pid].qty += qtyNum;
                        totalsMap[pid].sales += (qtyNum * priceNum) - discTotal;
                    }
                } else {
                    if (si.si_status !== 'cancelled') {
                        totalsMap[pid].qty += qtyNum;
                        totalsMap[pid].sales += (qtyNum * priceNum);
                    }
                }

                continue;
            }

            // multiple matches: group by qty+price and sum discounts per group
            const groups = [];
            for (const m of matches) {
                const qtyKey = (m.qty !== undefined && m.qty !== null) ? String(m.qty) : '';
                const priceVal = (typeof m.price !== 'undefined') ? m.price : '';
                const priceKey = (priceVal === '' || priceVal === null || typeof priceVal === 'undefined') ? '' : String(priceVal);
                // include unique_id in the grouping key to avoid mixing items that happen to share qty+price
                const uidKey = (m.unique_id !== undefined && m.unique_id !== null) ? String(m.unique_id) : '';
                const key = qtyKey + '|' + priceKey + '|' + uidKey;
                let g = groups.find(x => x.key === key);
                if (!g) {
                    g = { key: key, qty: qtyKey, price: priceVal, discSum: 0 };
                    groups.push(g);
                }
                // Treat m.discount as per-unit discount and accumulate total discount amount for the group
                const dPerUnit = (typeof m.discount === 'number') ? m.discount : (Number(m.discount) || 0);
                const mQtyNum = Number(m.qty) || 0;
                g.discSum += dPerUnit * mQtyNum;
            }

            row['qty_' + pid] = groups.map(g => g.qty);
            row['price_' + pid] = groups.map(g => (g.price === '' || g.price === null || typeof g.price === 'undefined') ? '' : g.price);
            if (p.has_discount) {
                // display per-unit discount for each group (if available), else blank
                row['disc_' + pid] = groups.map(g => {
                    const qtyNum = Number(g.qty) || 0;
                    if (!g.discSum || !qtyNum) return '';
                    // show per-unit discount (discSum divided by qty) for the group
                    return g.discSum / qtyNum;
                });
            }

            if (si.si_status !== 'cancelled') {
                for (const g of groups) {
                    const qtyNum = Number(g.qty) || 0;
                    const priceNum = Number(g.price) || 0;
                    const discNum = p.has_discount ? (g.discSum || 0) : 0; // discSum is total discount for this group
                    totalsMap[pid].qty += qtyNum;
                    totalsMap[pid].sales += qtyNum * priceNum - discNum;
                }
            }
        }

        return row;
    });

    const centerTargets = [];
    for (let i = 1; i < columns.length; i++) centerTargets.push(i);

    const totalAmountSum = tableData.reduce((s, r) => r.si_status === 'cancelled' ? s : s + (Number(r.total_amount) || 0), 0);

    var siTable = $('#si_table_accounting').DataTable({
        destroy: true,
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        dom: "<'dt-top-row'<'dt-left'l><'dt-center'B><'dt-right'f>>rt<'dt-bottom-row'<'dt-bottom-left'i><'dt-bottom-right'p>>",
                buttons: [
                    { extend: 'excel', text: 'Excel', className: 'excel-primary', filename: 'Sales Invoice Table', exportOptions: { columns: ':visible' } },
                ],
        data: tableData,
        order: [[1, 'asc']],
        columns: columns,
        createdRow: function (row, data) {
            if (data.si_status === 'cancelled') {
                $(row).css('text-decoration', 'line-through');
            }
        },
        columnDefs: [
            { targets: '_all', defaultContent: '' },
            { targets: centerTargets, className: 'text-center' },
            { targets: [0], className: 'text-left' }
        ],
        footerCallback: function (row, data, start, end, display) {
            const api = this.api();
            for (let i = 0; i < columns.length; i++) {
                const col = columns[i].data;
                if (!col) { $(api.column(i).footer()).html(''); continue; }

                const qtyMatch = String(col).match(/^qty_(\d+)$/);
                if (qtyMatch) {
                    const pid = Number(qtyMatch[1]);
                    const val = totalsMap[pid] ? totalsMap[pid].qty : 0;
                    $(api.column(i).footer()).html(val || '');
                    continue;
                }

                const priceMatch = String(col).match(/^price_(\d+)$/);
                if (priceMatch) {
                    const pid = Number(priceMatch[1]);
                    const val = totalsMap[pid] ? totalsMap[pid].sales : 0;
                    if (val) {
                        if (typeof formatPrice === 'function') {
                            $(api.column(i).footer()).html(formatPrice(val));
                        } else {
                            $(api.column(i).footer()).html(val.toFixed(2));
                        }
                    } else {
                        $(api.column(i).footer()).html('');
                    }
                    continue;
                }

                if (col === 'total_amount') {
                    if (totalAmountSum) {
                        if (typeof formatPrice === 'function') {
                            $(api.column(i).footer()).html(formatPrice(totalAmountSum));
                        } else {
                            $(api.column(i).footer()).html(totalAmountSum.toFixed(2));
                        }
                    } else {
                        $(api.column(i).footer()).html('');
                    }
                    continue;
                }

                $(api.column(i).footer()).html('');
            }
        }
    });

    // Ensure Buttons are placed correctly and center column has proper styling
    try {
        var style = document.getElementById('dt-custom-top-style');
        if (!style) {
            style = document.createElement('style');
            style.id = 'dt-custom-top-style';
            style.type = 'text/css';
            document.head.appendChild(style);
        }
        style.innerHTML = "\
            .dt-top-row{display:flex;align-items:center;gap:8px;margin-bottom:8px;}\n\
            .dt-top-row .dt-left{flex:1;text-align:left;}\n\
            .dt-top-row .dt-center{flex:0 0 auto;display:flex;justify-content:center;align-items:center;min-width:120px;}\n\
            .dt-top-row .dt-right{flex:1;text-align:right;}\n\
            /* Ensure Buttons container doesn't expand the center column */\n\
            .dt-top-row .dt-center .dt-button{margin:0 4px;}\n\
            /* Vanilla CSS for Excel primary button (no Bootstrap dependency) */\n\
            /* Make the Excel button pill-shaped and ensure it wins specificity */\n\
            .dt-button.excel-primary,\n\
            .dataTables_wrapper .dt-buttons .dt-button.excel-primary{\n\
                background-color:#0d6efd !important;\n\
                border:1px solid #0d6efd !important;\n\
                color:#fff !important;\n\
                padding:0.375rem 0.85rem;\n\
                border-radius:5px !important;\n\
                font-weight:500;\n\
                line-height:1.5;\n\
                box-shadow:0 1px 1px rgba(0,0,0,.05);\n\
                cursor:pointer;\n\
                overflow:hidden; /* ensure rounded corners clip any inner focus ring */\n\
            }\n\
            .dt-button.excel-primary:hover{\n\
                background-color:#0b5ed7 !important;\n\
                border-color:#0a58ca !important;\n\
                color:#fff !important;\n\
            }\n\
            .dt-button.excel-primary:focus{\n\
                outline:none;\n\
                box-shadow:0 0 0 0.2rem rgba(13,110,253,.25);\n\
            }\n\
            .dt-button.excel-primary:disabled, .dt-button.excel-primary.disabled{\n\
                opacity:.65;\n\
                pointer-events:none;\n\
            }\n\
            /* Bottom row: wrap info (left) and pagination (right) */\n\
            .dt-bottom-row{display:flex;align-items:center;gap:8px;margin-top:8px;}\n\
            .dt-bottom-left{flex:1;display:flex;align-items:center;justify-content:flex-start;}\n\
            .dt-bottom-right{flex:0 0 auto;display:flex;align-items:center;justify-content:flex-end;margin-left:auto;}\n\
            /* Ensure internal elements layout nicely */\n\
            .dt-bottom-left .dataTables_info{white-space:nowrap;}\n\
            .dt-bottom-right .dataTables_paginate{display:inline-flex;align-items:center;}\n\
            .dt-bottom-right .paginate_button{margin:0 2px;}\n\
            /* Responsive: stack on small widths */\n\
            @media (max-width:600px){\n\
                .dt-top-row, .dt-bottom-row{flex-direction:column;align-items:stretch;}\n\
                .dt-top-row .dt-center{order:2;margin:6px 0;}\n\
                .dt-bottom-left, .dt-bottom-right{justify-content:center;margin-left:0;}\n\
            }\n\
        ";

        // Move the buttons into the center wrapper if DataTables didn't already
        var wrapper = $('#si_table_accounting').closest('.dataTables_wrapper');
        var center = wrapper.find('.dt-center');
        if (center.length && wrapper.find('.dt-buttons').length) {
            center.append(wrapper.find('.dt-buttons'));
        }
    } catch (e) { /* ignore styling placement errors */ }

    // After init: copy header classes to cloned header and enable sticky header + frozen first two columns
    try {
        var wrapper = $('#si_table_accounting').closest('.dataTables_wrapper');
        var srcHead = $('#si_table_accounting thead tr');

        function copyHeaderClasses() {
            var clonedHead = wrapper.find('.dataTables_scrollHead table thead tr');
            clonedHead.find('th').each(function (i) {
                var cls = srcHead.find('th').eq(i).attr('class');
                if (cls) $(this).addClass(cls);
            });
        }

        copyHeaderClasses();
        siTable.on('draw', copyHeaderClasses);

        // apply sticky header and freeze first two columns
        function applySticky(tableSel, fixedCount) {
            const wrapper = $(tableSel).closest('.dataTables_wrapper');
            const srcHead = $(tableSel + ' thead tr');
            const bodyTable = wrapper.find('.dataTables_scrollBody table');

            wrapper.find('th, td').css('left', '');

            const leftOffsets = [];
            let acc = 0;
            for (let i = 0; i < fixedCount; i++) {
                const th = srcHead.find('th').eq(i);
                const w = th.outerWidth(true) || th.width() || 0;
                leftOffsets.push(acc);
                acc += w;
            }

            try { if (!$('#dt-sticky-styles').length) $('head').append('<style id="dt-sticky-styles">.dt-sticky-header th{position:sticky;top:0;z-index:1100;background:#fff}.dt-sticky-col{position:sticky;background:#fff;}</style>'); } catch (e) {}

            srcHead.addClass('dt-sticky-header');
            srcHead.find('th').each(function (i) {
                if (i < fixedCount) {
                    $(this).addClass('dt-sticky-col');
                    $(this).css({ left: leftOffsets[i] + 'px', 'z-index': 1110 });
                }
            });

            const clonedHead = wrapper.find('.dataTables_scrollHead table thead tr');
            clonedHead.find('th').each(function (i) {
                if (i < fixedCount) {
                    $(this).addClass('dt-sticky-col');
                    $(this).css({ left: leftOffsets[i] + 'px', 'z-index': 1115 });
                }
            });

            bodyTable.find('tr').each(function () {
                $(this).find('td').each(function (i) {
                    if (i < fixedCount) {
                        $(this).addClass('dt-sticky-col');
                        $(this).css({ left: leftOffsets[i] + 'px', 'z-index': 1100 });
                    }
                });
            });
        }

        applySticky('#si_table_accounting', 2);
        siTable.on('draw', function () { applySticky('#si_table_accounting', 2); });
        $(window).on('resize.dtsticky', function () { applySticky('#si_table_accounting', 2); });
    } catch (e) { /* ignore */ }

    // Prefer DataTables extensions if available: FixedHeader and FixedColumns
    try {
        if ($.fn.dataTable.FixedHeader) {
            new $.fn.dataTable.FixedHeader(siTable);
        }
        if ($.fn.dataTable.FixedColumns) {
            // leftColumns requires FixedColumns extension CSS/JS to be loaded
            new $.fn.dataTable.FixedColumns(siTable, { leftColumns: 2 });
        }
    } catch (e) {
        console.warn('SI: FixedHeader/FixedColumns init failed', e);
    }
}