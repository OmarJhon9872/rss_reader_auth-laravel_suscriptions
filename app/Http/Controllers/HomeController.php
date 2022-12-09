<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidateBasicSearchRequest;
use App\Http\Requests\ValidateUrlAddRssRequest;
use App\Http\Requests\VerifyUserRequest;
use App\Models\Category;
use App\Models\CategoryRssChannel;
use App\Models\Item;
use App\Models\ItemContent;
use App\Models\RoleUser;
use App\Models\RssChannel;
use App\Models\User;
use App\Traits\PaginationOfResultsTrait;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;

class HomeController extends Controller
{
    use PaginationOfResultsTrait;

    private const PER_PAGE_SEARCH = 9;
    private const PER_PAGE_SIMPLE_PAGINATION = 15;

    /**
     * Se necesita autenticacion para acceder al sistema
     */
    public function __construct(){
        if (config('auth.need_auth_rss')) {
            $this->middleware('auth');
        }
        #https://www.tandfonline.com/feed/rss/rabr20
        #https://www.elfinanciero.com.mx/arc/outboundfeeds/rss/?outputType=xml
        #https://link.springer.com/search.rss?query=artificial+intelligence
        #https://onlinelibrary.wiley.com/action/showFeed?ui=0&mi=3aa9pv4&type=search&feed=rss&query=%2526AllField%253Dartificial%252Bintelligence%2526content%253DarticlesChapters%2526target%253Ddefault
        #https://www.science.org/action/showFeed?type=etoc&feed=rss&jc=science
        #https://jamanetwork.com/rss/site_3/67.xml
        #https://www.elfinanciero.com.mx/arc/outboundfeeds/rss/?outputType=xml
        #https://www.reforma.com/rss/portada.xml
        #http://ep00.epimg.net/rss/cat/portada.xml
    }

    public function cambiar_analista(Request $request){
        $analista_from = $request->analista_from;
        $analista_to = $request->analista_to;

        $categorias = Category::where('user_id', $analista_from)->get();
        $categorias->each(function($categoria)use($analista_to){
            $categoria->user_id = $analista_to;
            $categoria->save();
        });

        $canales = RssChannel::where('user_id', $analista_from)->get();
        $canales->each(function($canal)use($analista_to){
            $canal->user_id = $analista_to;
            $canal->save();
        });

        return redirect()->route('home.usuarios_cliente')->with('status', 'Información migrada correctamente');
    }

    public function verifica_tiene_items(Request $request, User $usuario){
        $this->authorize('es_cliente');

        if($usuario->rss_channels->count() > 0){
            return response()->json([
                'status'    => 'not_empty'
            ]);
        }

        return response()->json([
            'status'    => 'ok'
        ]);
    }

    public function borrar_usuario(User $usuario){
        $this->authorize('es_cliente');

        if($usuario->rss_channels->count() > 0){
            return redirect()->route('home.usuarios_cliente')->withErrors('No es posible eliminar al usuario ya que tiene elementos cargados, migra sus elementos a otro analista antes de eliminarlo');
        }

        if($usuario->employees->count() > 0){
            return redirect()->route('home.usuarios_cliente')->withErrors('No es posible eliminar al usuario ya que tiene empleados activos');
        }

        $usuario->role->delete();

        $usuario->delete();

        return redirect()->route('home.usuarios_cliente')->with('status', 'Usuario borrado correctamente');
    }

    public function crear_usuario(VerifyUserRequest $request){
        $this->authorize('es_cliente');

        if(!Gate::check('es_super_admin')){
            $maximo_licencias = auth()->user()->role->licenses;

            $empleados_cliente= auth()->user()->employees->count();

            if(intval($maximo_licencias) < intval($empleados_cliente)+1){
                return redirect()->route('home.usuarios_cliente')->withErrors('No puedes crear mas usuarios, licencias insuficientes');
            }
        }
        $user = User::create([
            'name'     => $request->name,
            'last1'    => $request->last1,
            'last2'    => $request->last2,
            'email'    => $request->email,
            'password' => bcrypt($request->password)
        ]);

        if(Gate::check('es_super_admin')){
            RoleUser::create([
                'licenses' => $request->licenses,
                'role_id'  => 1,
                'user_id'  => $user->id
            ]);
            $tipo = "Cliente";
        }else{

            RoleUser::create([
                'owner_id' => auth()->id(),
                'role_id'  => $request->role,
                'user_id'  => $user->id
            ]);
            $tipo = "Usuario";
        }

        return redirect()->route('home.usuarios_cliente')->with('status', $tipo.' creado correctamente');
    }

    public function cambiar_clave_usuario(VerifyUserRequest $request, User $usuario){
        $this->authorize('es_cliente');

        $usuario->name = $request->name;
        $usuario->last1 = $request->last1;
        $usuario->last2 = $request->last2;
        $usuario->email = $request->email;

        $usuario->role->licenses = $request->licenses;
        $usuario->role->save();

        if($request->password != ''){
            $usuario->password = bcrypt($request->password);
        }

        $usuario->save();

        return redirect()->route('home.usuarios_cliente')->with('status', 'Usuario actualizado correctamente');
    }

    public function usuarios_cliente(){
        $this->authorize('es_cliente');

        if(Gate::check('es_super_admin')){
            $empleados = RoleUser::where('role_id', 1)->with(['user', 'desc_role'])->withCount('rss_channels')->get();
            $titulo = 'Clientes registrados';
        }else{
            $empleados = auth()->user()->employees()->with(['user', 'desc_role'])->withCount('rss_channels')->get();
            $titulo = 'Usuarios del cliente';
        }

        $roles = array(
            ['id'=>2, 'description'=>'Analista'],
            ['id'=>3, 'description'=>'Investigador']
        );

        $analistas = auth()->user()->employees()->where('role_id', 2)->with('user')->get()->toArray();

        return view('customer.users', [
            'empleados' => $empleados,
            'titulo'    => $titulo,
            'roles'    => $roles,
            'analistas'    => $analistas,
        ]);
    }

    public function actualizarCategoria(Request $request, Category $categoria){
        $categoria->name = $request->name;
        $categoria->description = $request->description;
        $categoria->save();

        return redirect()->back()->with('status', 'Categoria actualizada correctamente');
    }

    public function guardarCategoria(Request $request){
        Category::create([
            'user_id'     => auth()->id() ?? '0',
            'name'        => $request->name,
            'description' => $request->description
        ]);

        return redirect()->back()->with('status', 'Categoria creada correctamente');
    }

    public function verifica_nombre_categoria(Request $request){
        $nombre = $request->nombre;
        $existe = 'si';
        $exacta = 'no';
        $coincidencia = 'no';
        $coincidencia_valor = '';

        $existe_categoria = Category::where('name', 'like', '%'.$nombre.'%')->get();

        if(count($existe_categoria) == 0){
            $existe = 'no';
        }
        else{
            $existe_categoria = $existe_categoria->first();

            $coincidencia = 'si';
            if(strtoupper($existe_categoria['name']) == strtoupper($nombre)){
                $exacta = 'si';
            }else{
                $coincidencia_valor = $existe_categoria['name'];
            }
        }

        return response()->json([
            'status'             => 'ok',
            'coincidencia'       => $coincidencia,
            'coincidencia_valor' => $coincidencia_valor,
            'exacta'             => $exacta,
            'existe'             => $existe
        ], 200);

    }

    public function categorizar_elemento(Request $request, $id, $tipo_elemento){

        if($tipo_elemento == 'item'){
            $categorias_front = $request->categorias;

            $item = Item::findOrFail($id);

            $item->categories()->sync($categorias_front);
        }

        return redirect()->back()->with('status', 'Item categorizado correctamente');
    }

    public function eliminar_elemento($id, $tipo_elemento){
        $status = 'error';
        $message = '';

        if($tipo_elemento == 'item'){
            $item = Item::find($id);
            if($item != null){
                if($item->delete()) $status = 'ok';
            }else{
                $message = 'Elemento no encontrado';
            }
        }
        elseif ($tipo_elemento == 'categoria'){
            $category = Category::find($id);
            $proceder = 1;

            if($category == null){
                $proceder = 0;
                $message = 'Elemento no encontrado';
            }

            if($category->items->count() > 0){
                $proceder = 0;
                $message = 'Categoria con elementos, no es posible borrar';
            }

            if($proceder and $category->delete()) $status = 'ok';
        }
        elseif ($tipo_elemento == 'canal'){
            $canal = RssChannel::find($id);
            $proceder = 1;

            if($canal == null){
                $proceder = 0;
                $message = 'Elemento no encontrado';
            }

            if($canal->items->count() > 0){
                $proceder = 0;
                $message = 'Canal con elementos, no es posible borrar';
            }

            if($proceder and $canal->delete()) $status = 'ok';
        }


        return response()->json([
            'status' => $status,
            'message'=> $message
        ], 200);
    }

    public function buscar(Request $request){

        $resultado = [];

        if(strlen($request->buscar) > 2){
            $resaltar = "<script>$(function(){ $('*').highlight('".$request->buscar."');})</script>";
        }

        /*#######################################*/
        /*Busquedas exactas*/
        $resultado[] = RssChannel::where('channel_title', 'like', '%'.$request->buscar.'%')->get();

        $resultado[] = Category::where('name', 'like', '%'.$request->buscar.'%')
                        ->orWhere('description', 'like', '%'.$request->buscar.'%')->get();

        $resultado[] = Item::where('keywords', 'like', '%'.$request->buscar.'%')->with(['rss_channel', 'item_contents', 'categories'])->get();
        $resultado[] = Item::where('title', 'like', '%'.$request->buscar.'%')->with(['rss_channel', 'item_contents', 'categories'])->get();

        $resultado[] = Item::whereHas('item_contents', function($query)use($request){
            $query->where('value', 'like', '%'.$request->buscar.'%');
        })->with(['rss_channel', 'item_contents', 'categories'])->get();


        /*#######################################*/
        /*Busquedas refinadas por palabra*/
        $cantidad_palabras = explode(' ', $request->buscar);
        if(count($cantidad_palabras) > 1){
            foreach ($cantidad_palabras as $palabra){
                $resultado[] = RssChannel::where('channel_title', 'like', '%'.$palabra.'%')->get();

                $resultado[] = Category::where('name', 'like', '%'.$request->buscar.'%')
                    ->orWhere('description', 'like', '%'.$request->buscar.'%')->get();

                $resultado[] = Item::where('keywords', 'like', '%'.$palabra.'%')->with(['rss_channel', 'item_contents', 'categories'])->get();
                $resultado[] = Item::where('title', 'like', '%'.$palabra.'%')->with(['rss_channel', 'item_contents', 'categories'])->get();

                $resultado[] = Item::whereHas('item_contents', function($query)use($palabra){
                    $query->where('value', 'like', '%'.$palabra.'%');
                })->with(['rss_channel', 'item_contents', 'categories'])->get();

                if(strlen($palabra) > 2) {
                    $resaltar .= "<script>$(function(){ $('*').highlight('" . $palabra . "');})</script>";
                }
            }
        }


        $resultado = $this->joinArraysFromArray($resultado);
        /*Verificamos solo sean resultados unicos*/
        $resultado = collect($resultado)->unique()->unique()->toArray();


        $currentPage = Paginator::resolveCurrentPage('page');
        $perPage = $this::PER_PAGE_SEARCH;
        $ultimaPagina = ceil(count($resultado) / $perPage) == $currentPage && count($resultado) > 0 ? false:true;

        $resultados = $this->createCustomPagination(
            $resultado, $request->buscar, $perPage, 'buscar', 'page'
        );

        $resultados->hasMorePagesWhen($ultimaPagina);

        $inicio_mostrando = ($currentPage * $this::PER_PAGE_SEARCH)-($this::PER_PAGE_SEARCH - 1);
        $fin_mostrando = ($currentPage * $this::PER_PAGE_SEARCH);
        $fin_mostrando = count($resultado) < $this::PER_PAGE_SEARCH ? count($resultado): $fin_mostrando;

        $titulo = "Resultados para la busqueda: ".$request->buscar."<br>
                <small style='font-size: 0.6em;'>Mostrando registros: ".$inicio_mostrando." al ".$fin_mostrando." de un total de: ".count($resultado)."</small>"
                .$resaltar;

        $categorias = Category::all();

        return view('home', [
            'resultados' => $resultados,
            'titulo'     => $titulo,
            'categorias' => $categorias
        ]);
    }

    public function canales(RssChannel $canal){
        if($canal->toArray() == []){
            $resultados = RssChannel::latest()->with(['categories'])->simplePaginate($this::PER_PAGE_SIMPLE_PAGINATION);
            $view = "channels";
            $titulo = "Canales";
        }else{
            $resultados = $canal->items()->with(['categories'])->simplePaginate($this::PER_PAGE_SIMPLE_PAGINATION);
            $view = "home";
            $titulo = "Items del canal: ".$canal->channel_title;
        }

        $categorias = Category::all();

        return view($view, [
            'resultados' => $resultados,
            'titulo'     => $titulo,
            'categorias' => $categorias
        ]);
    }

    public function categorias(Category $categoria){

        if($categoria->toArray() == []){
            $resultados = Category::latest()->simplePaginate($this::PER_PAGE_SIMPLE_PAGINATION);
            $view = "categories";
            $titulo = "Categorias";
        }else{
            $channel_id = CategoryRssChannel::select(['rss_channel_id'])->whereCategoryId($categoria->id)->get();
            $resultados = Item::whereIn('rss_channel_id', $channel_id->pluck('rss_channel_id')->toArray())->latest()->simplePaginate($this::PER_PAGE_SIMPLE_PAGINATION);

            /*$resultados = $categoria->items()->latest()->simplePaginate($this::PER_PAGE_SIMPLE_PAGINATION);*/
            $view = "home";
            $titulo = "Entradas de categoria: ".$categoria->name;
        }

        $categorias = Category::all();

        return view($view, [
            'resultados' => $resultados,
            'titulo'     => $titulo,
            'categorias' => $categorias
        ]);
    }

    public function index(){
        $resultados = Item::latest()->with(['rss_channel', 'categories', 'item_contents'])->simplePaginate($this::PER_PAGE_SIMPLE_PAGINATION);
        $titulo = "Entradas";

        $categorias = Category::all();

        return view('home', [
            'resultados' => $resultados,
            'titulo'     => $titulo,
            'categorias' => $categorias
        ]);
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

    public function guardar_mostrar_campos(Request $request){
        $campos = $request->marcados;

        $item_id = $request->item_id;
        $channel_id = $request->channel_id;

        Item::whereRssChannelId($channel_id)->get()->each(function($item)use($campos){
            $item->item_contents->each(function($item_content)use($campos){
                if(!in_array($item_content->field, $campos)){
                    $item_content->delete();
                }
            });
        });

        return response()->json([
            'status' => 'ok',
            'data'   => [
                'channel_id' => $channel_id
            ]
        ], 200);
    }

    public function agregar_rss(ValidateUrlAddRssRequest $request){
        $url = $request->rss_url;
        $data = $this->getItemVersion2_0($url);

        $rss_channel = '';
        $item_db = '';
        $items_content = [];
        $resultado = [];

        /*Verificamos haya data en channel, items y nombre_archivo*/
        foreach ($data as $key => $value) {
            /*Si hay algun error se retorna en caso contrario se prepara la data*/
            if ($data[$key] == "url_error" || $data[$key] == "file_error" || $data[$key] == "no_content_valid") {

                $resultado['item_id'] = 'NO';
                $resultado['channel_id'] = 'NO';
                $resultado['fields_show'] = $value;

                return response()->json([
                    'status' => 'ok',
                    'data' => $resultado
                ]);
            }
            #if($key == 'items' || $key == 'channel'){
            if($key == 'items'){
                if(count($data[$key]) == 0){

                    $resultado['item_id'] = 'NO';
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

        if($verifica_existe != null){
            $rss_channel = $verifica_existe;
        }else{
            $rss_channel = RssChannel::create([
                'filename'            => $data['nombre_archivo'],
                'user_id'             => auth()->id() ?? '0',
                'channel_url'         => $data['channel']['link'] ?? '',
                'channel_title'       => $data['channel']['title'] ?? '',
                'channel_description' => strip_tags($data['channel']['description'] ?? '')
            ]);
        }

        /*Verificamos haya data en channel, items y nombre_archivo*/
        foreach ($data as $key_data => $value){

            if($key_data == 'items'){

                foreach ($data['items'] as $item) {
                    $keywords = [];
                    $description = '';

                    if(array_key_exists('description', $item)){
                        $keywords[] = $this->array_unique_from_string($item['description'], ' ');
                        $description = $item['description'];
                    }
                    if(array_key_exists('dc:description', $item)){
                        $keywords[] = $this->array_unique_from_string($item['dc:description'], ' ');
                        if(strlen($item['dc:description']) > strlen($description)){
                            $description = $item['dc:description'];
                        }
                    }
                    if(array_key_exists('content:encoded', $item)){
                        $keywords[] = $this->array_unique_from_string($item['content:encoded'], ' ');
                        if(strlen($item['content:encoded']) > strlen($description)){
                            $description = $item['content:encoded'];
                        }
                    }
                    $keywords[] = $this->array_unique_from_string($item['title'], ' ');

                    $keywords = $this->joinArraysFromArray($keywords);
                    $keywords = join(' ', $keywords);
                    $keywords = $this->array_unique_from_string($keywords, ' ');
                    $keywords = join(' ', $keywords);


                    /*Si el link no esta nulo, se actualiza solamente*/
                    if($item['link'] != ''){
                        $verifica_existe_item = Item::whereLink($item['link'])->first();

                        /*Si no se encuentra el item se crea con sus elementos, en caso contrario
                        se actualizan los campos de item y cada uno de itemContent*/
                        if($verifica_existe_item != null){
                            $item_db = $verifica_existe_item;
                            $item_db->rss_channel_id = $rss_channel->id;
                            $item_db->guid           = $item['guid'] ?? '';
                            $item_db->title          = $item['title'] ?? '';
                            $item_db->link           = $item['link'] ?? '';
                            $item_db->description    = $description;
                            $item_db->keywords       = $keywords;

                            $item_db->save();


                        }
                        /*En caso de que no se encontrara el item identificado por el link, se crea con
                        sus elemento correspondientes*/
                        else{
                            $item_db = Item::create([
                                'rss_channel_id' => $rss_channel->id,
                                'guid'           => $item['guid'] ?? '',
                                'title'          => $item['title'] ?? '',
                                'link'           => $item['link'] ?? '',
                                'description'    => $description,
                                'keywords'       => $keywords
                            ]);
                        }

                        /*Ya que se creo el item o actualizo segun el caso*/
                        $keys_item = array_keys($item);

                        foreach ($keys_item as $key_item) {

                            /*Si los demas campos ya guardados no estan presentes se guardan*/
                            if( $key_item != 'guid' &&
                                $key_item != 'title' &&
                                $key_item != 'link' &&
                                $key_item != 'description' &&
                                $key_item != 'dc:description' &&
                                $key_item != 'content:encoded'){

                                $items_content[] = ItemContent::create([
                                    'item_id'   => $item_db->id,
                                    'field'     => $key_item,
                                    'value'     => strip_tags($item[$key_item]),
                                    'name'      => $key_item,
                                    'showField' => 1
                                ]);
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

        $resultado['item_id'] = $item_db->id ?? 'NO';
        $resultado['channel_id'] = $rss_channel->id ?? 'NO';
        $resultado['fields_show'] = $uniques_fields;


        return response()->json([
            'status' => 'ok',
            'data'   => $resultado
        ], 200);
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
}
