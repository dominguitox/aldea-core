<?php
/**
 * Plugin Name: Aldea core
 * Description: Plugin Aldea core.
 * Version: 1.1
 * Author: Jorgelio
 */


//Funciones Taller
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

// Funciones