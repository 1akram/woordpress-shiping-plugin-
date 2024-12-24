jQuery(document).ready(function ($) {
  let orderDetails;
  $("#city").autocomplete({
    appendTo: "#custom-modal",
    source: function (request, response) {
      $.ajax({
        url: ajaxurl,
        dataType: "json",
        data: {
          name: request.term,
          action: "autocomplete_search",
        },
        success: function (data) {
          const availableCities = data.map((city) => {
            return { label: city.name_en, value: city.id };
          });

          response(availableCities);
        },
      });
    },

    select: function (event, ui) {
      $("#city").val(ui.item.label);
      cityId = ui.item.value;

      return false;
    },

    minLength: 2,
  });

  // Show modal on button click
  $(".ship-action").on("click", function (e) {
    e.preventDefault();
    $("#custom-modal").fadeIn();
    $("#modal-loading").show();
    $("#custom-modal-form").hide();

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
          console.log("Order details:", response.data.total);

          orderDetails = response.data;
          $("#reciever").val(
            `${orderDetails.shipping_first_name} ${orderDetails.shipping_last_name}`
          );
          $("#phone").val(orderDetails.billing_phone);
          $("#address").val(
            `${orderDetails.shipping_address_1} ${orderDetails.shipping_address_2}`
          );
          $("#city").val(orderDetails.billing_city);
          $("#modal-loading").hide();
          $("#custom-modal-form").fadeIn();
        } else {
          console.error("Error:", response.data);
          $("#modal-loading").hide();
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX request failed:", error);
        $("#modal-loading").hide();
      },
    });
  });

  // Close modal
  $("#custom-modal-close").on("click", function () {
    $("#custom-modal").fadeOut();
  });

  // Handle form submission
  $("#custom-modal-form").on("submit", function (e) {
    e.preventDefault();

    $("#modal-loading").show();
    $("#custom-modal-form").hide();
    $("#modal-title").hide();

    const leangh = 1;
    const width = 1;
    const height = 1;
    const paid_by = "customer";
    const commission_by = "customer";
    const extra_size_by = "customer";
    const payment_method = "cash";
    const formData = new FormData(this);

    const body = {
      reciever: formData.get("reciever"),
      address: `${orderDetails.billing_address_1} ${orderDetails.billing_address_2}`,
      payment_methode: payment_method,
      commission_by,
      qty: 1,
      phone: formData.get("phone"),
      price: orderDetails.total,
      paid_by,
      description: formData.get("description"),
      height,
      leangh,
      width,
      city_name: formData.get("city"),
      extra_size_by,
    };

    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "request_delivery",
        order_data: body,
      },
      success: function (response) {
        $("#modal-loading").hide();
        console.log("Form submitted successfully:", response);
        const successMessage = $(
          `<div class="notice notice-success is-dismissible">
          <p>${response.data.message}</p>
        </div>`
        );
        $("#custom-modal").prepend(successMessage);
        setTimeout(() => {
          successMessage.fadeOut(() => successMessage.remove());
          $("#custom-modal").fadeOut();
        }, 3000);
      },
      error: function (xhr, status, error) {
        $("#modal-loading").hide();
        console.error("Error submitting form:", error);
        const errorMessage = $(
          `<div class="notice notice-error is-dismissible">
          <p>Error submitting form. Please try again.</p>
        </div>`
        );
        $("#custom-modal").prepend(errorMessage);

        setTimeout(() => {
          errorMessage.fadeOut(() => errorMessage.remove());
          $("#custom-modal").fadeOut();
        }, 3000);
      },
    });
  });
});
