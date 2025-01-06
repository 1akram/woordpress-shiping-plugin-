jQuery(document).ready(function ($) {
  function recalculateDeliveryFees() {
    let field = $("#ship-to-different-address-checkbox").is(":checked")
      ? "#shipping_city"
      : "#billing_state";
    let selectedValue = $(field).val();
    console.log(selectedValue);

    $.ajax({
      url: ajax_object.ajax_url,
      type: "POST",
      data: {
        action: "recalculate_delivery_fees",
        selected_value: selectedValue,
      },
      success: function (response) {
        console.log(response);

        // Update totals or show messages as needed
        $("body").trigger("update_checkout");
      },
    });
  }

  // Watch for changes
  $("#billing_state, #shipping_city").on("change", function () {
    recalculateDeliveryFees();
  });

  $("#ship-to-different-address-checkbox").on("change", function () {
    recalculateDeliveryFees();
  });
});
