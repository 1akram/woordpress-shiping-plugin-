jQuery(document).ready(function ($) {
  // Show modal on button click

  $(".ship-action").on("click", function (e) {
    e.preventDefault();

    let orderId = $(this).attr("href").split("order_id=")[1];

    $.ajax({
      url: orderData.ajaxUrl,
      type: "POST",
      data: {
        action: "ship_order",
        order_id: orderId,
        nonce: orderData.nonce,
      },
      success: function (response) {
        if (response.success) {
          console.log("Order details:", response.data);

          let orderDetails = response.data;
          $("#description").val(orderDetails.id);
          $("#reciever").val(
            `${orderDetails.shipping_first_name} ${orderDetails.shipping_last_name}`
          );
          $("#date_created").val(orderDetails.date_created);
          $("#price").val(orderDetails.total);
          $("#currency").val(orderDetails.currency);
          $("#payment_method").val(orderDetails.payment_method);
          $("#payment_method_title").val(orderDetails.payment_method_title);
          $("#phone").val(orderDetails.billing_phone);
          $("#billing_first_name").val(orderDetails.billing_first_name);
          $("#billing_last_name").val(orderDetails.billing_last_name);
          $("#billing_email").val(orderDetails.billing_email);
          $("#address").val(
            `${orderDetails.billing_address_1} ${orderDetails.billing_address_2}`
          );
          $("#billing_city").val(orderDetails.billing_city);
          $("#billing_state").val(orderDetails.billing_state);
          $("#billing_postcode").val(orderDetails.billing_postcode);
          $("#billing_country").val(orderDetails.billing_country);
          $("#shipping_first_name").val(orderDetails.shipping_first_name);
          $("#shipping_last_name").val(orderDetails.shipping_last_name);
          $("#shipping_address_1").val(orderDetails.shipping_address_1);
          $("#shipping_address_2").val(orderDetails.shipping_address_2);
          $("#shipping_city").val(orderDetails.shipping_city);
          $("#shipping_state").val(orderDetails.shipping_state);
          $("#shipping_postcode").val(orderDetails.shipping_postcode);
          $("#shipping_country").val(orderDetails.shipping_country);

          console.log("Order ID:", orderDetails.id);
          console.log("Order Status:", orderDetails.status);
          console.log("Order Total:", orderDetails.total);
          console.log("Customer Name:", orderDetails.customer_name);
          // Do something with the order data (update UI, show a message, etc.)
        } else {
          console.error("Error:", response.data);
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX request failed:", error);
      },
    });
    $("#custom-modal").fadeIn();
  });

  // Close modal
  $("#custom-modal-close").on("click", function () {
    $("#custom-modal").fadeOut();
  });

  // Handle form submission
  $("#custom-modal-form").on("submit", function (e) {
    e.preventDefault();
    // const type = 1;
    // const package_sub_type = 6;
    const leangh = 1;
    const width = 1;
    const height = 1;
    // const breakable = 1;
    // const measuring_is_allowed = 1;
    // const inspection_allowed = 1;
    // const heat_intolerance = 1;
    // const casing = 1;
    // const paid_by = "customer";
    // const commission_by = "customer";
    // const extra_size_by = "customer";
    const payment_method = "cash";
    $("#custom-modal").fadeOut();

    const formData = $(this).serialize();
  });
});
