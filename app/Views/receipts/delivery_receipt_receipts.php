<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<style>
    @media print {
        .receipt_container {
            height: 900px;
        }
    }

    .receipt_container {
        height: 900px;
    }

    .receipt_margin {
        margin-top: 200px;
        margin-left: 65px;
    }

    .receipt_margin_bottom {
        margin-bottom: 65px;
    }

    #drr_client_name {
        font-size: 18px;
        margin-left: 160px;
        margin-bottom: 2px;
    }

    #drr_date {
        font-size: 18px;
        margin-left: 65px;
    }

    #drr_date_year {
        font-size: 18px;
        margin-left: 10px;
    }

    #drr_tin {
        font-size: 18px;
        margin-left: 160px;
        margin-bottom: 2px;
    }

    #drr_term {
        font-size: 18px;
        margin-left: 80px;
    }

    #drr_address {
        font-size: 18px;
        margin-left: 90px;
        margin-bottom: 2px;
    }

    #drr_business_name {
        font-size: 18px;
        margin-left: 90px;
    }

    #item_lists {
        margin-top: 40px;
        margin-left: 115px;
    }

    #item_lists p {
        font-size: 16px;
    }
</style>

<div class="row">
    <div class="col-10 receipt_container d-flex flex-column justify-content-between">
        <div class="receipt_margin">
            <div class="row">
                <div class="col-8">
                    <p id="drr_client_name"></p>
                </div>
                <div class="col-3">
                    <p id="drr_date"></p>
                </div>
                <div class="col-1">
                    <p id="drr_date_year"></p>
                </div>
                <div class="col-8">
                    <p id="drr_tin"></p>
                </div>
                <div class="col-4">
                    <p id="drr_term"></p>
                </div>
                <div class="col-12">
                    <p id="drr_address"></p>
                </div>
                <div class="col-8">
                    <p id="drr_business_name"></p>
                </div>
                <div class="col-4">

                </div>
            </div>
            <div class="" id="item_lists">
                
            </div>
        </div>


        <div class="receipt_margin_bottom">
            <div class="">
                <div class="row">
                    <div class="col-1">
                    </div>
                    <div class="col-2">
                    </div>
                    <div class="col-5">
                    </div>
                    <div class="col-2">
                    </div>
                    <div class="col-2">
                        <p id="total_amount"></p>
                    </div>
                </div>
            </div>
            <div class="">
                <div class="row">
                    <div class="col-1">
                    </div>
                    <div class="col-2">
                    </div>
                    <div class="col-5 d-flex justify-content-end">
                        <p id="discount_title">DISCOUNT</p>
                    </div>
                    <div class="col-2">
                    </div>
                    <div class="col-2">
                    </div>
                </div>
            </div>
            <div class="" id="item_discounts">
            </div>
            <div class="" id="freight_container">
                <div class="row">
                    <div class="col-1">
                    </div>
                    <div class="col-2">
                    </div>
                    <div class="col-5 d-flex d-flex justify-content-end">
                        <p>TPA&nbsp;</P>
                    </div>
                    <div class="col-2">
                    </div>
                    <div class="col-2">
                        <p id="freight_cost"></p>
                    </div>
                </div>
            </div>
            <div class="">
                <div class="row">
                    <div class="col-1">
    
                    </div>
                    <div class="col-2" id="re-printed">

                    </div>
                    <div class="col-5">
    
                    </div>
                    <div class="col-2">
    
                    </div>
                    <div class="col-2">
                        <p id="total_amount_due" style="border-top: 1px solid black; width: 75%;"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/my_js/global.js') ?>"></script>
<script>
    // Get the JSON data from the hidden input field
    let data = <?= $result ?>;
    let header = data.header;
    let item_summary = data.items;
    let print_status = <?= $print_status ?>;

    $(document).ready(function() {
        $("#drr_client_name").text(header.client_name == "" ? "." : header.client_name);
        $("#drr_date").text(header.dr_date == "" ? "." : formatDateMonthDay(header.dr_date));
        $("#drr_date_year").text(header.dr_date == "" ? "." : new Date(header.dr_date).getFullYear().toString().slice(2));
        $("#drr_tin").text(header.client_tin == "" ? "." : header.client_tin);
        $("#drr_term").text(header.client_term == "" ? "." : terms_set(header.client_term));
        $("#drr_address").text(header.client_address == "" ? "." : header.client_address);
        $("#drr_business_name").text(header.client_business_name == "" ? "." : header.client_business_name);
        $("#freight_cost").text(header.freight_cost == "" ? "." : formatPrice(header.freight_cost));
        if (!header.freight_cost || header.freight_cost == 0) {
            $("#freight_container").hide();
        }
        item_print_list_table(item_summary);
        // Hide discount title if there are no discounts
        let hasDiscount = false;
        item_summary.forEach(function(item) {
            if (item.discounts && item.discounts.length > 0) {
                hasDiscount = true;
            }
        });
        if (!hasDiscount) {
            $('#discount_title').hide();
        }
        checkPrintStatus(print_status);

        window.print();
    });

    function item_print_list_table(item_summary) {
        item_summary.forEach(function(item) {
            let item_row = `<div class="row"><div class="col-1"><p>${item.qty}</p></div>` +
                `<div class="col-2"><p>${item.product_unit}</p></div>` +
                `<div class="col-6"><p>${item.product_name} (${item.product_code})</p></div>` +
                `<div class="col-3"><p>${formatPrice(item.price)}&nbsp;&nbsp;&nbsp;&nbsp;${formatPrice(item.price * item.qty)}</p></div></div>`;
            $("#item_lists").append(item_row);
        });

        compute_printed_vatables(item_summary);
    }

    function compute_printed_vatables(data) {
        $('#item_discounts').empty();
        let sum_tot_amnt = 0; 
        let sum_tot_disc = 0;
        let sum_tot_amnt_due = 0;

        data.forEach(function (item) {
            sum_tot_amnt = sum_tot_amnt + (parseFloat(item.price) * parseFloat(item.qty));
            sum_disc = show_printed_discount_summary(item.discounts, parseFloat(item.qty));
            sum_tot_amnt_due = sum_tot_amnt_due + ((parseFloat(item.price) * parseFloat(item.qty)) - sum_disc);
        });

        sum_tot_amnt_due = parseFloat(sum_tot_amnt_due) + (header.freight_cost === '' ? 0 : parseFloat(header.freight_cost));

        $("#total_amount").text(sum_tot_amnt == 0 ? formatPrice(0) : formatPrice(sum_tot_amnt));
        $("#total_amount_due").text(sum_tot_amnt_due == 0 ? formatPrice(0) : formatPrice(sum_tot_amnt_due));
    }

    function show_printed_discount_summary(dis_data, qty) {
        let discount_row = '';
        let disc_value = 0;
        dis_data.forEach(function (dis) {
            if (dis.value == '') {
                disc_value += 0;
            }
            else {
                discount_row = '<div class="row"><div class="col-1"></div><div class="col-2"></div>' +
                    '<div class="col-5 d-flex justify-content-end"><p>' + dis.label + '&nbsp;' + dis.value + ' x ' + qty + '</p></div>' +
                    '<div class="col-2"></div>' +
                    '<div class="col-2"><p>' + formatPrice(dis.value * qty) + '</p></div></div>';
                $("#item_discounts").append(discount_row);
            }
            disc_value += (dis.value * qty);
        });

        return disc_value;
    }

    function checkPrintStatus(status) {
        if (status === "printed") {
            $("#re-printed").append('<p style="font-size: 17px;">RE PRINTED</p>');
        }
    }

    function formatDateYearMonthDayDashed(dateString) {
        const date = new Date(dateString);
        if (isNaN(date)) {
            return "Invalid Date";
        }

        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    }

    function formatDateMonthDay(dateString) {
        const date = new Date(dateString);
        if (isNaN(date)) {
            return "Invalid Date";
        }

        const monthNames = ["JANUARY", "FEBRUARY", "MARCH", "APRIL", "MAY", "JUNE", "JULY", "AUGUST", "SEPTEMBER", "OCTOBER", "NOVEMBER", "DECEMBER"];
        const month = monthNames[date.getMonth()];
        const day = date.getDate();

        return `${month} ${day},`;
    }
</script>
<?= $this->endSection() ?>