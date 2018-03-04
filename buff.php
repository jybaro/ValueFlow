<?php


                ///////////
                // Costos:
                //
                $campos_valores['COSTO_CAPACIDAD_CONTRATADA'] = $campos_valores['CAPACIDAD_CONTRATADA'] * $campos_valores['COSTO_MB'];
                $campos_valores['COSTO_CAPACIDAD_FACTURADA']  = $campos_valores['CAPACIDAD_FACTURADA']  * $campos_valores['COSTO_MB'];
                $campos_valores['COSTO_CAPACIDAD_SOLICITADA'] = $campos_valores['CAPACIDAD_SOLICITADA'] * $campos_valores['COSTO_MB'];

                $campos_valores['COSTO_CAPACIDAD'] = $campos_valores['COSTO_CAPACIDAD_CONTRATADA'];
                $campos_valores['COSTO_MENSUAL'] = $campos_valores['COSTO_CAPACIDAD'];
                $campos_valores['COSTO_BW'] = $campos_valores['COSTO_CAPACIDAD'];
                $campos_valores['COSTO_BW_SOLICITADA'] = $campos_valores['COSTO_CAPACIDAD_SOLICITADA'];
                $campos_valores['COSTO_ACTUAL'] = $campos_valores['COSTO_CAPACIDAD'];

                //reemplaza los costos de instalacion de los nodos (nodo_nod_costo_) por los de la atención (nodo_costo_):
                if (isset($campos_valores['NODO_COSTO_INSTALACION_CLIENTE']) || isset($campos_valores['EXTREMO_COSTO_INSTALACION_CLIENTE'])) {
                    $campos_valores['EXTREMO_NOD_COSTO_INSTALACION_CLIENTE'] = isset($campos_valores['NODO_COSTO_INSTALACION_CLIENTE']) ? $campos_valores['NODO_COSTO_INSTALACION_CLIENTE'] : $campos_valores['EXTREMO_COSTO_INSTALACION_CLIENTE'] ;
                }
                if (isset($campos_valores['CONCENTRADOR_COSTO_INSTALACION_CLIENTE'])) {
                    $campos_valores['CONCENTRADOR_NOD_COSTO_INSTALACION_CLIENTE'] = $campos_valores['CONCENTRADOR_COSTO_INSTALACION_CLIENTE'];
                }
                $campos_valores['NODO_NOD_COSTO_INSTALACION_CLIENTE'] = $campos_valores['EXTREMO_NOD_COSTO_INSTALACION_CLIENTE'];

                $campos_valores['COSTO_INSTALACION'] = isset($campos_valores['COSTO_INSTALACION']) ? $campos_valores['COSTO_INSTALACION'] : $campos_valores['NODO_NOD_COSTO_INSTALACION_CLIENTE'];
                //$campos_valores['SUBTOTAL_SERVICIO'] = $campos_valores['COSTO_CAPACIDAD'] + $campos_valores['NODO_NOD_COSTO_INSTALACION_CLIENTE'];
                $campos_valores['SUBTOTAL_SERVICIO_COSTO'] = $campos_valores['COSTO_CAPACIDAD_CONTRATADA'] + $campos_valores['COSTO_INSTALACION'];
                $campos_valores['IVA_SERVICIO_COSTO'] = round($campos_valores['SUBTOTAL_SERVICIO_COSTO'] * $iva, 2);
                $campos_valores['TOTAL_SERVICIO_COSTO'] = $campos_valores['SUBTOTAL_SERVICIO_COSTO'] + $campos_valores['IVA_SERVICIO_COSTO'];

                $campos_valores['IVA_INSTALACION_COSTO'] = round($campos_valores['COSTO_INSTALACION'] * $iva, 2);
                $campos_valores['TOTAL_INSTALACION_COSTO'] = $campos_valores['COSTO_INSTALACION'] + $campos_valores['IVA_INSTALACION_COSTO'];

                $campos_valores['IVA_MENSUAL_COSTO'] = round($campos_valores['COSTO_CAPACIDAD'] * $iva, 2);
                $campos_valores['TOTAL_MENSUAL_COSTO'] = $campos_valores['COSTO_CAPACIDAD'] + $campos_valores['IVA_MENSUAL_COSTO'];

                $campos_valores['IVA_MENSUAL_SOLICITADO_COSTO'] = round($campos_valores['COSTO_CAPACIDAD_SOLICITADA'] * $iva, 2);
                $campos_valores['TOTAL_MENSUAL_SOLICITADO_COSTO'] = $campos_valores['COSTO_CAPACIDAD_SOLICITADA'] + $campos_valores['IVA_MENSUAL_SOLICITADO_COSTO'];

                
                $campos_valores['COSTO_TOTAL'] = (isset($campos_valores['CAPACIDAD_CONTRATADA'])?$campos_valores['CAPACIDAD_CONTRATADA'] : 0) * (isset($campos_valores['COSTO_MB'])?$campos_valores['COSTO_MB'] : 0);

