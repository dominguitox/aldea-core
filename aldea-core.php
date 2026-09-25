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