<?php
function plugin_ticketprice_install()
{
    global $DB;

    if (!$DB->fieldExists('glpi_tickets', 'price')) {
        $DB->query("ALTER TABLE glpi_tickets ADD COLUMN price DECIMAL(10,2) NOT NULL DEFAULT '0.00'") or die($DB->error());
    }

    return true;
}

function plugin_ticketprice_uninstall()
{
    global $DB;

    if ($DB->fieldExists('glpi_tickets', 'price')) {
        $DB->query("ALTER TABLE glpi_tickets DROP COLUMN price");
    }

    return true;
}

function plugin_ticketprice_item_form(array $params)
{
    if ($params['item'] instanceof Ticket) {
        global $CFG_GLPI;

        $price = isset($params['item']->fields['price']) ? $params['item']->fields['price'] : '0.00';

        $is_new_ticket = !$params['item']->getID();
        $formatted_price = $is_new_ticket
            ? htmlentities($price)
            : htmlentities(number_format($price, 2, ',', '.'));

        echo "<div class='form-field row col-12'>";
        echo "<label class='col-form-label col-xxl-4 text-xxl-end' for='price'>" . __('Preço R$', 'ticketprice') . "</label>";
        echo "<div class='col-xxl-8 field-container'>";
        echo "<input type='string' name='price' id='price' class='form-control' value='" . $formatted_price . "' />";
        echo "</div>";
        echo "</div>";

        echo "<script>
                document.getElementById('price').addEventListener('keyup', function (e) {
                const input = e.target;
                let value = input.value;

                value = value.replace(/\D/g, '');
                value = (value/100).toFixed(2) + '';
                value = value.replace('.', ',');
                value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');

                input.value = value;
                });

                document.getElementById('price').addEventListener('focusout', function (e) {
                    const input = e.target;
                    let value = input.value;

                    // Remove 'R$ ' e mantém apenas números e separadores válidos
                    value = value.replace('R$ ', '').replace(/\./g, '').replace(',', '.');

                    // Valida e corrige o valor apenas se for numérico
                    const numericValue = parseFloat(value);
                    if (!isNaN(numericValue)) {
                        input.value = numericValue.toFixed(2); // Garante sempre duas casas decimais
                    } else {
                        input.value = ''; // Limpa o campo caso o valor não seja válido
                    }
                });
            </script>";
    }
}

function plugin_ticketprice_item_update($item)
{
    if (get_class($item) === 'Ticket' && isset($_POST['price'])) {
        $price = str_replace(['R$', '.', ','], ['', '', '.'], $_POST['price']);
        $price = filter_var($price, FILTER_VALIDATE_FLOAT);

        if ($price !== false) {
            $item->fields['price'] = $price;
            $item->update([
                'id'    => $item->getID(),
                'price' => $price
            ]);
        }
    }
}

function plugin_ticketprice_display_solved_ticket(array $params)
{
    if ($params['item'] instanceof Ticket) {
        global $CFG_GLPI;

        $price = isset($params['item']->fields['price']) ? $params['item']->fields['price'] : '0.00';
        $status = isset($params['item']->fields['status']) ? $params['item']->fields['status'] : '';
        $description = isset($params['item']->fields['content']) ? $params['item']->fields['content'] : '';

        $status_solved = CommonITILObject::SOLVED; // Ou o valor específico do status "solucionado" no GLPI
        if ($status == $status_solved) {
            $formatted_price = number_format($price, 2, ',', '.');

            echo "
            <script>
                alert('teste');

                function addPriceToContent() {
                    var readOnlyContent = document.querySelector('.read-only-content .rich_text_container p');
                    if (readOnlyContent) {
                        readOnlyContent.textContent += ' | Preço: R$ ' + " . json_encode($formatted_price) . ";
                        return true; // Indica que o elemento foi encontrado e modificado
                    }
                    return false; // Indica que o elemento ainda não foi encontrado
                }

                if (!addPriceToContent()) {
                    var observer = new MutationObserver(function(mutations) {
                        if (addPriceToContent()) {
                            observer.disconnect(); // Para de observar após a modificação
                        }
                    });

                    observer.observe(document.body, { childList: true, subtree: true });
                }
            </script>
            ";
        }
    }
}