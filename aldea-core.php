<?php
/**
 * Plugin Name: Aldea core
 * Description: Plugin Aldea core.
 * Version: 1.1
 * Author: Jorgelio
 */


//Funciones Taller
function aldea_extraer_id_imagen_pod($img_field)
{
    if (is_array($img_field) && isset($img_field['ID']))
        return $img_field['ID'];
    if (is_array($img_field) && !empty($img_field))
        return $img_field[0]['ID'] ?? $img_field[0];
    if (is_numeric($img_field))
        return $img_field;
    return false;
}
function imprimir_etiqueta_cupos($id_taller = null)
{
    if (!$id_taller) {
        $id_taller = get_the_ID();
    }
    if (!$id_taller)
        return;
    $esta_suspendido = get_post_meta($id_taller, 'esta_suspendido', true);
    $es_libre = get_post_meta($id_taller, 'es_libre', true);
    $tiene_meta = metadata_exists('post', $id_taller, 'cupos');
    $cupos_original = get_post_meta($id_taller, 'cupos', true);
    $cupos = (int) $cupos_original;
    $cupos_usados = (int) get_post_meta($id_taller, 'cupos_usados', true);
    if ($esta_suspendido) {
        echo '<span class="tag negro">Suspendido</span>';
    } else {
        if ($es_libre || ($tiene_meta && $cupos_original !== '' && $cupos === 0)) {
            echo '<span class="tag verde">Con cupos</span>';
        } elseif ($tiene_meta && $cupos_original !== '' && $cupos_usados < $cupos) {
            $disponibles = $cupos - $cupos_usados;
            echo '<span class="tag azul">' . $disponibles . ' Cupos Disponibles</span>';
        } else {
            echo '<span class="tag naranjo">Cupos llenos</span>';
        }
    }
}
function imprimir_horario($id_taller = null, $sufijo = '')
{
    if (!$id_taller) {
        $id_taller = get_the_ID();
    }
    if (!$id_taller) {
        return;
    }
    $dia = get_post_meta($id_taller, 'dia' . $sufijo, true);
    $hora_inicio = get_post_meta($id_taller, 'hora_inicio' . $sufijo, true);
    $hora_termino = get_post_meta($id_taller, 'hora_termino' . $sufijo, true);

    if (empty($dia) || empty($hora_inicio)) {
        return;
    }

    $dia_limpio = htmlspecialchars($dia, ENT_QUOTES, 'UTF-8');
    $inicio_formateado = htmlspecialchars(date('H:i', strtotime($hora_inicio)), ENT_QUOTES, 'UTF-8');

    $html = '<li><strong>Horario:</strong> ' . $dia_limpio . ' ';

    if (!empty($hora_termino)) {
        $termino_formateado = htmlspecialchars(date('H:i', strtotime($hora_termino)), ENT_QUOTES, 'UTF-8');
        $html .= 'de ' . $inicio_formateado . ' a ' . $termino_formateado;
    } else {
        $html .= 'a las ' . $inicio_formateado;
    }
    $html .= '</li>';
    echo $html;
}

// Funciones evento

// Funciones alianza

function imprimir_galeria_rotatoria($id_objeto = null)
{
    $imagenes = get_post_meta(get_the_ID(), 'galeria', false);

    if (!empty($imagenes)) {
        if (isset($imagenes[0]) && is_array($imagenes[0])) {
            $imagenes = $imagenes[0];
        }

        $urls_imagenes = [];
        foreach ($imagenes as $imagen) {
            $img_id = is_array($imagen) ? $imagen['ID'] : $imagen;
            if (is_numeric($img_id)) {
                $url = wp_get_attachment_image_url($img_id, 'medium_large');
                if ($url)
                    $urls_imagenes[] = $url;
            }
        }

        if (!empty($urls_imagenes)):
            ?>
            <div id="contenedor-carrusel-<?php the_ID(); ?>"
                style="position: relative; max-width: 600px; height: 300px; border-radius: 8px; overflow: hidden; background: #eaeaea;">
                <img id="rotador-<?php the_ID(); ?>" src="<?php echo esc_url($urls_imagenes[0]); ?>"
                    style="width: 100%; height: 100%; object-fit: cover; transition: opacity 0.5s ease-in-out;">

                <?php if (count($urls_imagenes) > 1): ?>
                    <button id="btn-prev-<?php the_ID(); ?>"
                        style="position: absolute; top: 50%; left: 10px; transform: translateY(-50%); background: rgba(0,0,0,0.5); color: white; border: none; padding: 10px 15px; cursor: pointer; border-radius: 4px; font-size: 18px;">&#10094;</button>
                    <button id="btn-next-<?php the_ID(); ?>"
                        style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); background: rgba(0,0,0,0.5); color: white; border: none; padding: 10px 15px; cursor: pointer; border-radius: 4px; font-size: 18px;">&#10095;</button>
                <?php endif; ?>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const urls = <?php echo json_encode($urls_imagenes); ?>;
                    const contenedor = document.getElementById('contenedor-carrusel-<?php the_ID(); ?>');
                    const imgEl = document.getElementById('rotador-<?php the_ID(); ?>');
                    const btnPrev = document.getElementById('btn-prev-<?php the_ID(); ?>');
                    const btnNext = document.getElementById('btn-next-<?php the_ID(); ?>');
                    let i = 0;
                    let intervalo;

                    function cambiarImagen(index) {
                        imgEl.style.opacity = 0;
                        setTimeout(() => {
                            imgEl.src = urls[index];
                            imgEl.style.opacity = 1;
                        }, 500);
                    }

                    function iniciarIntervalo() {
                        clearInterval(intervalo);
                        intervalo = setInterval(() => {
                            i = (i + 1) % urls.length;
                            cambiarImagen(i);
                        }, 6000);
                    }

                    if (urls.length > 1) {
                        iniciarIntervalo();

                        btnNext.addEventListener('click', () => {
                            i = (i + 1) % urls.length;
                            cambiarImagen(i);
                            iniciarIntervalo();
                        });

                        btnPrev.addEventListener('click', () => {
                            i = (i - 1 + urls.length) % urls.length;
                            cambiarImagen(i);
                            iniciarIntervalo();
                        });

                        // Detiene la rotación al poner el mouse encima
                        contenedor.addEventListener('mouseenter', () => {
                            clearInterval(intervalo);
                        });

                        // Reanuda la rotación al quitar el mouse
                        contenedor.addEventListener('mouseleave', () => {
                            iniciarIntervalo();
                        });
                    }
                });
            </script>
            <?php
        endif;
    }
}