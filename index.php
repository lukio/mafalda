<?php
/** 
 * Nombre archivo: Index.php
 * Todos los action deberian de pasar por el index.php
 *
 * Anteriormente
 * header('location:http://fileserver/09-01-06/consulta.html');
 */

//creamos el codigo puente
if(!session_is_registered('user_autenticado')){
   /**
    * Usuario anónimo
    * Esta seteado el $_GET['action'?
    *  No -> Primera vez, entonces escribo la pagina
    *  Si -> viene de una consulta, evaluo el action.
    **/

    //if(!defined($_GET['action']){
    /**
    * El action no esta definido.
    * Primera vez que carga la páge
    **/
        require_once 'include/pear/Sigma.php'; //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('theme.html'); //seleccionamos la plantilla

        $data_der = array (
                        "Numero serie:  ", "text", "celda", "10", "celda_id",
                    );

        $data_cen = array (
                       "Lote produccion:  ", "text", "lote_produccion", "10", "lote_produccion_id",
                       "Lote embalado: &nbsp;&nbsp;", "text", "lote_embalado", "10", "lote_embalado_id", 
                    );

        for($i=0 ; $i < count($data_der);) {
            $it->setCurrentBlock('input_der'); //buscamos bloque

            $it->setVariable('DATO', $data_der[$i++]);
            $it->setVariable('TYPE_DATO', $data_der[$i++]);
            $it->setVariable('NAME_DATO',$data_der[$i++]);
            $it->setVariable('SIZE_DATO',$data_der[$i++]);
            $it->setVariable('ID_DATO',$data_der[$i++]);

            $it->parseCurrentBlock('input_der'); //generamos la parte del bloque analizado
        }

        for($i=0 ; $i < count($data_cen);) {
            $it->setCurrentBlock('input_cen'); //buscamos bloque

            $it->setVariable('DATO', $data_cen[$i++]);
            $it->setVariable('TYPE_DATO', $data_cen[$i++]);
            $it->setVariable('NAME_DATO',$data_cen[$i++]);
            $it->setVariable('SIZE_DATO',$data_cen[$i++]);
            $it->setVariable('ID_DATO',$data_cen[$i++]);

            $it->parseCurrentBlock('input_cen'); //generamos la parte del bloque analizado
        }



        $it->show(); //mostramos el resultado
        
      //  }else{
            /**
             * Evaluo el $_GET['action']
             **/
   // }

}else{
    /* Usuario autentificado */
    header('location:');
    session_start();
}        

?>
