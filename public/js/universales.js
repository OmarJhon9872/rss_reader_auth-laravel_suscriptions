makePostRequest = function(url, dataSend, isForUploadFiles){
    /*Si el parametro es pasado processData se colocara en false al igual
    * que contentType, esto hara que la peticion no se force conversion a
    * texto plano y se pueda procesar un envio multimedia */

    info = '';

    $.ajax({
        url: url,
        dataType: 'json',
        type: 'post',
        async: false,
        headers: {'X-CSRF-TOKEN': $("[name='csrf-token']").attr('content')},
        processData: isForUploadFiles,
        contentType: isForUploadFiles,
        data: dataSend,
    }).done(function(data){
        if(data){
            info = data;
        }
    });

    return info;
}

