<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemContent;
use App\Models\RssChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PruebasController extends Controller
{




    /*$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$*/
    /*$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$*/
    /*$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$*/
    /*$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$*/
    /*$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$*/
    /*$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$*/

    public function rss_masivo(Request $request){
        #$url = $request->rss_url;
        Auth::loginUsingId(9);

        $urls = explode("\n", $request->rss_url);
        $unique_urls = collect($urls)->unique();


        $rss_channel = '';
        $item_db = '';
        $items_content = [];
        $resultado = [];
        $errores_urls_usuario = [];
        $indice_error = 0;

        foreach ($urls as $url) {

            if($url == "") continue;

            $data = $this->getItemVersion2_0($url);


            /*  POSIBLES RESPUESTAS DE PROCESAMIENTO RSS
            $tdb['channel'] = "file_error";
            $tdb['items'] = "file_error";
            $tdb['nombre_archivo'] = "file_error";

            $tdb['channel'] = "url_error";
            $tdb['items'] = "url_error";
            $tdb['nombre_archivo'] = "url_error";

            $tdb['channel'] = "no_content_valid";
            $tdb['items'] = "no_content_valid";
            $tdb['nombre_archivo'] = "no_content_valid";

            SI HAY ALGUN ERROR SE AGREGA A LISTA DE URLS NO VALIDOS */
            if($data['channel'] == "file_error" or $data['channel'] == "url_error" or $data['channel'] == "no_content_valid"){
                $errores_urls_usuario[$indice_error]['url'] = $url;
                $errores_urls_usuario[$indice_error]['mensaje'] = "Error de url, favor de verificar: ".$data['channel'].", contacta a soporte o corrige url";

                $indice_error ++;
                continue;
            }

            /*Verificamos haya data en channel, items y nombre_archivo*/
            foreach ($data as $key => $value) {
                /*Si hay algun error se retorna en caso contrario se prepara la data*/
                if ($data[$key] == "url_error" || $data[$key] == "file_error" || $data[$key] == "no_content_valid") {

                    /*$resultado['item_id'] = 'NO';*/
                    $resultado['channel_id'] = 'NO';
                    $resultado['fields_show'] = $value;

                    return response()->json([
                        'status' => 'ok',
                        'data' => $resultado
                    ]);
                }
                #if($key == 'items' || $key == 'channel'){
                if ($key == 'items') {
                    if (count($data[$key]) == 0) {

                        /*$resultado['item_id'] = 'NO';*/
                        $resultado['channel_id'] = 'NO';
                        $resultado['fields_show'] = 'no_valid_items';

                        return response()->json([
                            'status' => 'ok',
                            'data' => $resultado
                        ]);
                    }
                }
            }

            /*Si hay data en channel, items y nombre_archivo*/
            /*PROCESAMOS CANAL SOLO SI ES UNICO, SI NO SE ADJUNTA A SUS ITEMS*/
            $verifica_existe = RssChannel::whereChannelUrl($data['channel']['link'])->first();

            if ($verifica_existe != null) {
                $rss_channel = $verifica_existe;
            } else {
                $rss_channel = RssChannel::create([
                    'filename' => $data['nombre_archivo'],
                    'user_id' => auth()->id() ?? '0',
                    'channel_url' => $data['channel']['link'] ?? '',
                    'channel_title' => $data['channel']['title'] ?? '',
                    'channel_description' => strip_tags($data['channel']['description'] ?? '')
                ]);
            }

            /*Verificamos haya data en channel, items y nombre_archivo*/
            foreach ($data as $key_data => $value) {

                if ($key_data == 'items') {

                    foreach ($data['items'] as $item) {
                        $keywords = [];
                        $description = '';

                        if (array_key_exists('description', $item)) {
                            $keywords[] = $this->array_unique_from_string($item['description'], ' ');
                            $description = $item['description'];
                        }
                        if (array_key_exists('dc:description', $item)) {
                            $keywords[] = $this->array_unique_from_string($item['dc:description'], ' ');
                            if (strlen($item['dc:description']) > strlen($description)) {
                                $description = $item['dc:description'];
                            }
                        }
                        if (array_key_exists('content:encoded', $item)) {
                            $keywords[] = $this->array_unique_from_string($item['content:encoded'], ' ');
                            if (strlen($item['content:encoded']) > strlen($description)) {
                                $description = $item['content:encoded'];
                            }
                        }
                        $keywords[] = $this->array_unique_from_string($item['title'], ' ');

                        $keywords = $this->joinArraysFromArray($keywords);
                        $keywords = join(' ', $keywords);
                        $keywords = $this->array_unique_from_string($keywords, ' ');
                        $keywords = join(' ', $keywords);


                        /*Si el link no esta nulo, se actualiza solamente*/
                        if ($item['link'] != '') {
                            $verifica_existe_item = Item::whereLink($item['link'])->first();

                            /*Si no se encuentra el item se crea con sus elementos, en caso contrario
                            se actualizan los campos de item y cada uno de itemContent*/
                            if ($verifica_existe_item != null) {
                                $item_db = $verifica_existe_item;
                                $item_db->rss_channel_id = $rss_channel->id;
                                $item_db->guid = $item['guid'] ?? '';
                                $item_db->title = $item['title'] ?? '';
                                $item_db->link = $item['link'] ?? '';
                                $item_db->description = $description;
                                $item_db->keywords = $keywords;

                                $item_db->save();


                            } /*En caso de que no se encontrara el item identificado por el link, se crea con
                            sus elemento correspondientes*/
                            else {
                                $item_db = Item::create([
                                    'rss_channel_id' => $rss_channel->id,
                                    'guid' => $item['guid'] ?? '',
                                    'title' => $item['title'] ?? '',
                                    'link' => $item['link'] ?? '',
                                    'description' => $description,
                                    'keywords' => $keywords
                                ]);
                            }

                            /*Ya que se creo el item o actualizo segun el caso*/
                            $keys_item = array_keys($item);

                            foreach ($keys_item as $key_item) {

                                /*Si los demas campos ya guardados no estan presentes se guardan*/
                                if ($key_item != 'guid' &&
                                    $key_item != 'title' &&
                                    $key_item != 'link' &&
                                    $key_item != 'description' &&
                                    $key_item != 'dc:description' &&
                                    $key_item != 'content:encoded') {

                                    $items_content[] = ItemContent::create([
                                        'item_id' => $item_db->id,
                                        'field' => $key_item,
                                        'value' => strip_tags($item[$key_item]),
                                        'name' => $key_item,
                                        'showField' => 1
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }

        $uniques_fields = collect($items_content)->pluck('field')->unique()->values();

        if(count($uniques_fields) == 0 && $item_db != ''){
            $uniques_fields = 'not_more_fields';
        }

        /*$resultado['item_id'] = $item_db->id ?? 'NO';*/
        $resultado['channel_id'] = $rss_channel->id ?? 'NO';
        $resultado['fields_show'] = $uniques_fields;
        $resultado['errores'] = $errores_urls_usuario;

        return response()->json([
            'status' => 'ok',
            'data'   => $resultado
        ], 200);
    }



    public function getItemVersion2_0($url){
        $tdb = array();
        try {
            $id_record = 1;

            $name_xml = time()." record_".$id_record.".xml";
            $path_xml = './xml/'.$name_xml;

            $xml = file_get_contents($url);

            file_put_contents($path_xml, $xml);

            $data = @file_get_contents($path_xml);
            if($data === false) {
                $tdb['channel'] = "file_error";
                $tdb['items'] = "file_error";
                $tdb['nombre_archivo'] = "file_error";
                return $tdb;
            }
        } catch(\Exception $e) {

            $tdb['channel'] = "url_error";
            $tdb['items'] = "url_error";
            $tdb['nombre_archivo'] = "url_error";
            return $tdb;
        }

        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        // $values and $tags are references and return data.
        xml_parse_into_struct($parser, $data, $values, $tags);
        xml_parser_free($parser);

        $values = $values;
        $tags = $tags;

        $channelSaved = false;
        foreach($tags as $key=>$val) {
            if($key == "item") {
                // each contiguous pair of array entries are the
                // lower and upper range for each item definition
                for($i=0; $i < count($val); $i+=2) {
                    $offset = $val[$i] + 1;
                    $len = $val[$i + 1] - $offset;
                    $tdb['items'][] = $this->parseVal(array_slice($values, $offset, $len));
                }
            }

            if($key == "channel" && !$channelSaved){ //Si es channel la etiqueta obtenemos su contenido para guardar lo necesario
                //Count de 2 porque solo debera haber un canal por rss etiquetas de apertura y cierre

                if(count($val) == 2){

                    //Inicio de tag ex: channel
                    $offset = $val[0] + 1;            //1   -- 2
                    //Cierre de tag ex: channel
                    $len = $val[1] - $offset;     //162 -- 160
                    //Obtenemos el contenido del tag
                    $valorrrrrrs = array_slice($values, $offset, $len);

                    $data_channel = [];
                    //Nivel al que la etiqueta debe de estar
                    $level_required = 0;
                    foreach ($valorrrrrrs as $value_inside_tag) {

                        //Para verificar que los datos del nivel pertenecen al tag corrrecto, tag channel nivel 3 inside
                        if(key_exists('level', $value_inside_tag)){
                            if($level_required == 0){
                                $level_required = $value_inside_tag['level'];
                            }
                        }

                        if(key_exists('tag', $value_inside_tag) and key_exists('value', $value_inside_tag) and key_exists('level', $value_inside_tag)){

                            //Se verifico que el array tenga los elementos requeridos, tag, level y value para guardar lo necesario
                            foreach(['title', 'link', 'description'] as $required_field){
                                if($value_inside_tag['tag'] == $required_field and $level_required == $value_inside_tag['level']){
                                    $tdb['channel'][$required_field] = $value_inside_tag['value'];
                                }
                            }

                            if(key_exists('title', $data_channel) and key_exists('link', $data_channel) and key_exists('description', $data_channel)){
                                $channelSaved = true;
                                break;
                            }
                        }
                    }
                }
            }
        }

        $tdb['nombre_archivo'] = $path_xml;

        if(count($tdb) != 3){

            $tdb['channel'] = "no_content_valid";
            $tdb['items'] = "no_content_valid";
            $tdb['nombre_archivo'] = "no_content_valid";
            return $tdb;
        }

        return $tdb;
    }


    public function array_unique_from_string($string, $separator = ' '){
        $string = strip_tags($string);
        $conjunto = explode($separator, $string);
        $elementos = [];
        foreach ($conjunto as $item){
            if(strlen($item) > 2){
                $item = $this->eliminar_tildes($item);
                $item = preg_replace('([^A-Za-z0-9])', ' ', $item);
                $elementos[] = strtoupper($item);
            }
        }

        return array_unique($elementos);
    }


    public function joinArraysFromArray($array_to_join){
        $result = [];
        foreach ($array_to_join as $items_index) {
            foreach ($items_index as $item) {
                $result[] = $item;
            }
        }

        return $result;
    }


    private function parseVal($vals) {
        for ($i=0; $i < count($vals); $i++) {
            if(!array_key_exists("value", $vals[$i])){
                continue;
            }
            $val[$vals[$i]["tag"]] = $vals[$i]["value"];
        }
        return $val;
    }


    function eliminar_tildes($cadena){

        //Codificamos la cadena en formato utf8 en caso de que nos de errores
        $cadena = utf8_encode($cadena);

        //Ahora reemplazamos las letras
        $cadena = str_replace(
            array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
            array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
            $cadena
        );

        $cadena = str_replace(
            array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
            array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
            $cadena );

        $cadena = str_replace(
            array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
            array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
            $cadena );

        $cadena = str_replace(
            array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
            array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
            $cadena );

        $cadena = str_replace(
            array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
            array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
            $cadena );

        return $cadena;
    }
    /*$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$*/
    /*$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$*/
    /*$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$*/
    /*$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$*/
    /*$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$*/
    /*$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$*/

















}
