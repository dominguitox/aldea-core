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
function imprimir_horario($id_objeto = null, $sufijo = '')
{
    if (!$id_objeto) {
        $id_objeto = get_the_ID();
    }
    if (!$id_objeto) {
        return;
    }

    $frecuencia = get_post_meta($id_objeto, 'frecuencia' . $sufijo, true);
    $fecha = get_post_meta($id_objeto, 'fecha' . $sufijo, true);
    $dia_legacy = get_post_meta($id_objeto, 'dia' . $sufijo, true);

    $texto_dia = '';
    if (!empty($frecuencia)) {
        $texto_dia = $frecuencia;
    } elseif (!empty($fecha)) {
        $texto_dia = $fecha; // (Para Talleres)
    } elseif (!empty($dia_legacy)) {
        $texto_dia = $dia_legacy;
    }

    $hora_inicio = get_post_meta($id_objeto, 'hora_inicio' . $sufijo, true);
    $hora_termino = get_post_meta($id_objeto, 'hora_termino' . $sufijo, true);

    if (empty($texto_dia) || empty($hora_inicio)) {
        return;
    }

    $dia_limpio = htmlspecialchars($texto_dia, ENT_QUOTES, 'UTF-8');
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
function imprimir_info_precio_taller($id_taller = null)
{
    if (!$id_taller) {
        $id_taller = get_the_ID();
    }
    if (!$id_taller) {
        return;
    }

    $es_libre = get_post_meta($id_taller, 'es_libre', true);
    $es_gratuito = get_post_meta($id_taller, 'es_gratuito', true);
    $valor = get_post_meta($id_taller, 'valor', true);

    $html = '<div style="margin-top: 20px; padding: 15px; background: #fff; border: 1px solid #dbdbdb; border-left: 4px solid #03c4a1 !important; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">';

    if ($es_libre) {
        $html .= '<h4 style="margin-top: 0;">Actividad Libre</h4>';
        if ($es_gratuito) {
            $html .= '<p style="margin-bottom: 0;">Este taller es de acceso libre y gratuito. ¡Te esperamos!</p>';
        } elseif ($valor) {
            $html .= '<p style="margin-bottom: 5px;">Actividad de acceso libre (sin inscripción previa).</p>';
            $html .= '<p style="margin-bottom: 0;"><strong>Valor:</strong> ' . esc_html($valor) . '</p>';
        } else {
            $html .= '<p style="margin-bottom: 0;">Actividad de acceso libre. ¡Te esperamos!</p>';
        }
    } else {
        $html .= '<h4 style="margin-top: 0;">Información de Inscripción</h4>';
        if ($es_gratuito) {
            $html .= '<p><strong>Valor:</strong> ¡Gratuito! (Requiere inscripción previa)</p>';
        } elseif ($valor) {
            $html .= '<p><strong>Valor:</strong> ' . esc_html($valor) . '</p>';
        }
        $html .= '<p style="margin-bottom: 0; font-size: 0.9em; color: #666;"><em>Cupos limitados. Inscríbete para asegurar tu lugar.</em></p>';
    }

    $html .= '</div>';

    echo $html;
}
// Funciones evento

// Funciones alianza

function imprimir_galeria_rotatoria($id_objeto = null)
{
    //la galeria debe llamarse 'galeria'
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

                <?php foreach ($urls_imagenes as $index => $url): ?>
                    <img class="rotador-img-<?php the_ID(); ?>" src="<?php echo esc_url($url); ?>"
                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; transition: opacity 0.3s ease-in-out; opacity: <?php echo $index === 0 ? '1' : '0'; ?>; z-index: <?php echo $index === 0 ? '1' : '0'; ?>;">
                <?php endforeach; ?>

                <?php if (count($urls_imagenes) > 1): ?>
                    <button id="btn-prev-<?php the_ID(); ?>"
                        style="position: absolute; top: 50%; left: 10px; transform: translateY(-50%); background: rgba(0,0,0,0.5); color: white; border: none; padding: 10px 15px; cursor: pointer; border-radius: 4px; font-size: 18px; z-index: 10;">&#10094;</button>
                    <button id="btn-next-<?php the_ID(); ?>"
                        style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); background: rgba(0,0,0,0.5); color: white; border: none; padding: 10px 15px; cursor: pointer; border-radius: 4px; font-size: 18px; z-index: 10;">&#10095;</button>
                <?php endif; ?>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const contenedor = document.getElementById('contenedor-carrusel-<?php the_ID(); ?>');
                    const imagenes = document.querySelectorAll('.rotador-img-<?php the_ID(); ?>');
                    const btnPrev = document.getElementById('btn-prev-<?php the_ID(); ?>');
                    const btnNext = document.getElementById('btn-next-<?php the_ID(); ?>');
                    let actual = 0;
                    let total = imagenes.length;
                    let intervalo;

                    function cambiarImagen(siguiente) {
                        // Oculta la actual
                        imagenes[actual].style.opacity = 0;
                        imagenes[actual].style.zIndex = 0;

                        // Muestra la nueva
                        imagenes[siguiente].style.opacity = 1;
                        imagenes[siguiente].style.zIndex = 1;

                        actual = siguiente;
                    }

                    function iniciarIntervalo() {
                        clearInterval(intervalo);
                        intervalo = setInterval(() => {
                            let siguiente = (actual + 1) % total;
                            cambiarImagen(siguiente);
                        }, 6000);
                    }

                    if (total > 1) {
                        iniciarIntervalo();

                        btnNext.addEventListener('click', () => {
                            let siguiente = (actual + 1) % total;
                            cambiarImagen(siguiente);
                            iniciarIntervalo();
                        });

                        btnPrev.addEventListener('click', () => {
                            let siguiente = (actual - 1 + total) % total;
                            cambiarImagen(siguiente);
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