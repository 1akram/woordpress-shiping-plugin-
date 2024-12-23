jQuery(document).ready(function ($) {
  $(".city-edit-field").on("change", function () {
    const cityId = $(this).data("id");
    const columnName = $(this).data("column");
    const newValue = $(this).val();

    $.post(
      transportCompanyAjax.ajaxUrl,
      {
        action: "update_city",
        nonce: transportCompanyAjax.nonce,
        city_id: cityId,
        column_name: columnName,
        value: newValue,
      },
      function (response) {
        if (response.success) {
          // alert("City updated successfully!");
        } else {
          alert("Failed to update city: " + response.data);
        }
      }
    );
  });
});
