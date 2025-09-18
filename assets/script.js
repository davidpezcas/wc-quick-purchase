jQuery(document).ready(function ($) {
  const popup = $("#wc-quick-purchase-popup");
  const attributesContainer = $(".wcqp-attributes");
  const priceDisplay = $(".wcqp-price");

  function renderAttributes() {
    attributesContainer.empty();
    const attributes = window.wcqpData.attributes;

    Object.keys(attributes).forEach((attrName) => {
      // Obtener options y convertir a array si es un objeto tipo {0: ..., 6: ...}
      let options = attributes[attrName];
      if (!Array.isArray(options) && typeof options === "object") {
        options = Object.values(options);
      }
      options = Array.isArray(options) ? options : [];

      const label = attrName.replace("pa_", "").toUpperCase();

      const select = $("<select>")
        .attr("name", attrName)
        .addClass("wcqp-attr-select");
      select.append(
        $("<option>").text(`Selecciona ${label}`).attr("value", "")
      );

      options.forEach((option) => {
        select.append($("<option>").attr("value", option).text(option));
      });

      const wrapper = $('<div class="wcqp-attribute-group">')
        .append($("<label>").text(label))
        .append(select);

      attributesContainer.append(wrapper);
    });
  }

  // Buscar la variación seleccionada según los atributos escogidos
  function findMatchingVariation(selected) {
    if (!window.wcqpData || !window.wcqpData.variations) return null;

    return (
      window.wcqpData.variations.find((variation) => {
        const isMatch = Object.keys(selected).every((attr) => {
          const key = `attribute_${attr}`;
          const expected = ((variation.attributes[key] || "") + "")
            .toLowerCase()
            .trim();
          const given = ((selected[attr] || "") + "").toLowerCase().trim();

          console.log(`Comparando ${key}: "${expected}" vs "${given}"`);

          return expected === given;
        });

        // Aquí imprimes la comparación completa de la variación con la selección
        console.log(
          "Comparando variación completa:",
          variation.attributes,
          "con selección:",
          selected,
          "→ Coincide?",
          isMatch
        );

        return isMatch;
      }) || null
    );
  }

  //NUEVO: Captura de datos del formulario
  $("#wcqp-form").on("submit", function (e) {
    e.preventDefault();

    const qty = $("#wcqp-qty").val();
    const name = $("#wcqp-name").val();
    const lastname = $("#wcqp-lastname").val();
    const phone = $("#wcqp-phone").val();
    const email = $("#wcqp-email").val();
    const address = $("#wcqp-address").val();
    const city = $("#wcqp-city").val();
    const total = $("#wcqp-total-input").val();

    const selectedAttributes = {};
    $(".wcqp-attr-select").each(function () {
      let attrName = $(this).attr("name").toLowerCase().trim();
      if (!attrName.startsWith("pa_")) {
        attrName = "pa_" + attrName; // WooCommerce usa prefijo pa_
      }

      const attrValue = $(this).val().toLowerCase().trim();
      if (attrValue) selectedAttributes[attrName] = attrValue;
    });

    // Llamada AJAX
    $.ajax({
      url: wcqp_ajax.ajax_url,
      type: "POST",
      data: {
        action: "wcqp_create_order",
        product_id: window.wcqpData.productId,
        qty,
        name,
        lastname,
        phone,
        email,
        address,
        city,
        attributes: selectedAttributes,
        total,
      },
      success: function (response) {
        if (response.success) {
          console.log("Pedido creado (test):", response.data);

          // Datos para WhatsApp
          const productName =
            document
              .querySelector(
                ".product_title, .entry-title, h1.product_title, h1.wp-block-post-title"
              )
              ?.textContent.trim() || "Producto";

          const qty = $("#wcqp-qty").val();
          const name = $("#wcqp-name").val();
          const lastname = $("#wcqp-lastname").val();
          const phone = $("#wcqp-phone").val();
          const email = $("#wcqp-email").val();
          const address = $("#wcqp-address").val();
          const city = $("#wcqp-city").val();
          const total = $("#wcqp-total-input").val();

          // Variaciones seleccionadas
          const selectedAttributes = [];
          $(".wcqp-attr-select").each(function () {
            const label = $(this).prev("label").text();
            const value = $(this).val();
            if (value) selectedAttributes.push(`${label}: ${value}`);
          });

          const variationText = selectedAttributes.length
            ? selectedAttributes.join(", ")
            : "N/A";

          // Construir el mensaje con %0A para saltos de línea
          const message =
            `Nuevo pedido rápido%0A` +
            `Producto: ${productName}%0A` +
            `Variación: ${variationText}%0A` +
            `Cantidad: ${qty}%0A` +
            `Cliente: ${name} ${lastname} %0A` +
            `Email: ${email}%0A` +
            `Teléfono: ${phone}%0A` +
            `Direccion: ${address}%0A` +
            `Ciudad: ${city}%0A` +
            `Total pedido: ${total}`;

          // Número quemado para WhatsApp (cámbialo por el tuyo)
          //const whatsappNumber = "573168236599";

          // Número dinámico de WhatsApp desde WordPress
          const whatsappNumber = mpbData.whatsappNumber;

          // Abrir WhatsApp Web
          const whatsappUrl = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(
            decodeURIComponent(message)
          )}`;
          window.open(whatsappUrl, "_blank");

          //Limpiar el formulario y cerrar popup
          $("#wcqp-form")[0].reset();
          $("#wc-quick-purchase-popup").fadeOut(200);

          /* alert(
            `Pedido creado correctamente (ID: ${response.data.order_id}). Se abrió WhatsApp para enviar el mensaje.`
          ); */
        } else {
          console.error("Error:", response.data);
          alert("Error al crear el pedido: " + response.data.message);
        }
      },

      error: function (xhr) {
        console.error("Error AJAX:", xhr);
        alert("Hubo un error en el servidor.");
      },
    });
  });

  // Actualizar precio cuando cambian los selectores
  attributesContainer.on("change", ".wcqp-attr-select", function () {
    const selected = {};
    $(".wcqp-attr-select").each(function () {
      let name = $(this).attr("name").toLowerCase().trim();
      if (!name.startsWith("pa_")) {
        name = "pa_" + name;
      }
      const value = $(this).val().toLowerCase().trim();
      if (value) selected[name] = value;
    });

    console.log("Atributos seleccionados:", selected);
    console.log(
      "Variaciones originales:",
      window.wcqpData.variations.map((v) => v.attributes)
    );

    const variation = findMatchingVariation(selected);
    console.log("Variación encontrada:", variation);

    if (variation) {
      console.log("Precio formateado recibido:", variation.formatted_price);
      const priceHtml =
        variation.price_html ||
        variation.formatted_price ||
        `Precio: ${variation.display_price}`;
      priceDisplay.html(priceHtml);
    } else {
      priceDisplay.html("Seleccione opciones para ver el precio");
    }
  });

  // Abrir popup y renderizar atributos
  $(".wc-quick-purchase-btn").on("click", function () {
    renderAttributes();
    priceDisplay.html(
      `Precio: ${
        $(this).closest("form").find(".woocommerce-Price-amount").html() || ""
      }`
    );
    popup.fadeIn();
  });

  $(document).on("click", ".wcqp-close", function () {
    popup.fadeOut();
  });
  popup.on("click", function (e) {
    if (e.target === this) popup.fadeOut();
  });

  // === ACTUALIZAR TOTAL DINÁMICO ===
  function updateTotal() {
    const qty = parseInt($("#wcqp-qty").val()) || 1;

    // Obtenemos el precio mostrado en el popup
    let priceText = $(".wcqp-price")
      .text()
      .replace(/[^\d.,]/g, "")
      .replace(".", "")
      .replace(",", ".");
    let price = parseFloat(priceText) || 0;

    const total = qty * price;
    $("#wcqp-total-amount").text(`$${total.toLocaleString("es-CO")}`);
    $("#wcqp-total-input").val(total);
  }

  // Eventos para recalcular el total
  $("#wcqp-qty").on("input change", updateTotal);
  $(".wcqp-attributes").on("change", "select", updateTotal);
});
