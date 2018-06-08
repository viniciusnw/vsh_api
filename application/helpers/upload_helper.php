<?php
    
function do_single_upload($config, &$error, &$data, $form_name = "userfile", $thumb_config = FALSE)
{
        $CI = &get_instance();        

        $CI->load->library('upload', $config);

        if ( ! $CI->upload->do_upload($form_name))
        {
                $error = array('error' => $CI->upload->display_errors());
                return FALSE;
        }  
        else
        {
                $data = $CI->upload->data();
                if($thumb_config){
                	do_thumb($thumb_config, $data['file_name'], $config['upload_path']);
                }
                return $data['file_name'];
        }
}

function do_multiple_upload($config, &$erros, &$dados, $form_name = "userfile", $max_upload_files = FALSE,  $thumb_config = FALSE) {
    $CI = &get_instance();
    $CI->load->library('upload', $config);

    $files = $_FILES;

    $cpt = count ( $_FILES [$form_name] ['name'] );    
       
    if($max_upload_files && $cpt > $max_upload_files){    	
    	$erros = "Número máximo de uploads excedido";
    	return FALSE;  
    }
    
    for($i = 0; $i < $cpt; $i ++) {

        $_FILES [$form_name] ['name'] = $files [$form_name] ['name'] [$i];
        $_FILES [$form_name] ['type'] = $files [$form_name] ['type'] [$i];
        $_FILES [$form_name] ['tmp_name'] = $files [$form_name] ['tmp_name'] [$i];
        $_FILES [$form_name] ['error'] = $files [$form_name] ['error'] [$i];
        $_FILES [$form_name] ['size'] = $files [$form_name] ['size'] [$i];       
        
        if ( ! $CI->upload->do_upload($form_name)){
        	$erros[] = array($_FILES [$form_name] ['name'] => $CI->upload->display_errors());
        	//return FALSE;
        }
        else{
        	$dados_foto = $CI->upload->data();    
        	$dados[] = $dados_foto;
        	if($thumb_config){
        		do_thumb($thumb_config, $dados_foto['file_name'], $config['upload_path']);
        	}
        }        
	}
	return TRUE;
}

function do_thumb($thumb_config, $file_name, $upload_path)
{
	$CI = &get_instance();	
	
	$thumb_config['source_image'] = $upload_path ."/". $file_name;
	$CI->load->library('image_lib');	
// 	$CI->image_lib->initialize($thumb_config);
// 	$CI->image_lib->resize();
// 	$CI->image_lib->clear();
	
	$aux_name = explode(".", $file_name);
	$file_thumb_name = $aux_name[0] . "_thumb." . $aux_name[1];	
	$file_crop_name = str_replace("_thumb", "_crop", $file_thumb_name);
	
	$thumb_config['source_image'] = $upload_path ."/". $file_name;
	$thumb_config['new_image'] = $upload_path ."/". $file_crop_name;
	$thumb_config['maintain_ratio'] = FALSE;
	$thumb_config['create_thumb'] = FALSE;
	if( isset($thumb_config['crop-width'])){
		$thumb_config['width'] = $thumb_config['crop-width'];
	}
	if( isset($thumb_config['crop-height'])){
		$thumb_config['height'] = $thumb_config['crop-height'];
	}
	
	$img = open_image(base_url(str_replace("./", "", $thumb_config['source_image'])));
	$cx = imagesx($img) / 2;
	$cy = imagesy($img) / 2;
	$x = $cx - $thumb_config['width'] / 2;
	$y = $cy - $thumb_config['height'] / 2;
	if ($x < 0) $x = 0;
	if ($y < 0) $y = 0;
	
	$thumb_config['x_axis'] = $x;
	$thumb_config['y_axis'] = $y;
	
	$CI->image_lib->clear();
	$CI->image_lib->initialize($thumb_config);
	
	$CI->image_lib->crop();
}

function open_image ($file) {
	
	$size=getimagesize($file);
	switch($size["mime"]){
		case "image/jpeg":
			$im = imagecreatefromjpeg($file); //jpeg file
			break;
		case "image/gif":
			$im = imagecreatefromgif($file); //gif file
			break;
		case "image/png":
			$im = imagecreatefrompng($file); //png file
			break;
		default:
			$im=false;
			break;
	}
	return $im;
}

function upload_base64_image($img, $config, &$error = FALSE)
{
	$dados = explode(";", $img);	
	$tipo = explode(":", $dados[0]);
	$arquivo = str_replace("base64,", "", $dados[1]);	
	
	switch($tipo[1]){
		case "image/jpeg":
			$nome_arquivo = create_unique_random_filename($config['upload_path'], ".jpg");
			file_put_contents($config['upload_path'].$nome_arquivo, base64_decode($arquivo));
			break;
		case "image/gif":
			$nome_arquivo = create_unique_random_filename($config['upload_path'], ".gif");
			file_put_contents($config['upload_path'].$nome_arquivo, base64_decode($arquivo));
			break;
		case "image/png":
			$nome_arquivo = create_unique_random_filename($config['upload_path'], ".png");
			file_put_contents($config['upload_path'].$nome_arquivo, base64_decode($arquivo));
			break;
		default:
			$error = "Tipo de arquivo não permitido.";
			break;
	}
	
	return $nome_arquivo;
}

function get_dropzone_html($form_action)
{
	$html = '
	 	<div class="ibox-content">
            <form id="my-awesome-dropzone" class="dropzone" action="'. $form_action . '">
                <div class="dropzone-previews"></div>
                <button type="submit" class="btn btn-primary pull-right">Enviar</button>
                <div class="dz-message text-center" data-dz-message>
                    <span>
                        <p><img src="'. base_url('assets/img/upload_icon.png').'" width="200px" class="img-responsive center-block"></p>
                        <h2>Clique ou arraste para subir os arquivos</h2>
                    </span>
                </div>
                
            </form>
            <!-- Texto explicativo
            <div>
                <div class="m text-right"><small>Escreva algo</small> </div>
            </div>
            -->
        </div>
    ';

    echo $html;
}

function get_dropzone_js($config, $max_files)
{
	$html = '
	 	Dropzone.options.myAwesomeDropzone = {

                autoProcessQueue: false,
                uploadMultiple: true,
                parallelUploads: 10,
                maxFiles: '.$max_files.',
                paramName: "userfile", // The name that will be used to transfer the file
                maxFilesize: '.$config['max_size'].', // MB
                acceptedFiles: "'.$config['allowed_types_dropzone'].'",
                dictMaxFilesExceeded: "Você pode carregar somente '.$max_files.' fotos",
                dictDefaultMessage: "Drop the images you want to upload here",
                dictFileTooBig: "Imagem muito grande! Tamanho maximo permitido é '.$config['max_size'].' MB",

                // Dropzone settings
                init: function() {
                    var myDropzone = this;

                    this.element.querySelector("button[type=submit]").addEventListener("click", function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        myDropzone.processQueue();
                    });
                    this.on("sendingmultiple", function() {
                    });
                    this.on("successmultiple", function(files, response) { 

                    });
                    this.on("errormultiple", function(files, response) {
                    });
                    this.on("queuecomplete", function(file) {    

                    });
					this.on("complete", function(file) {
					  //this.removeFile(file);
					});
                }

            }
    ';

    echo $html;
}


function create_unique_random_filename($dir, $type)
{
	while (true) {
		$filename = uniqid(rand(), true) . $type;
		if (!file_exists($dir . $filename)) break;
	}
	return $filename;
}



?>
