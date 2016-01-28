/**
 * 
 */

/*Vorschau leeren bei neuwahl*/
var uploadfiles = document.querySelector('#fileinput');
uploadfiles.addEventListener('click', function () {
    //alert('KLICKED');
    var node = document.getElementById('gallery');
    while (node.hasChildNodes()) {
        node.removeChild(node.lastChild);
    }

}, false);


/*Bilder hinzu bei Dateiwahl*/
uploadfiles.addEventListener('change', function () {
    var files = this.files;
    for(var i=0; i<files.length; i++){
        previewImage(this.files[i]);
    }

}, false);



/*Klick auf Button UPLOAD*/
function upload_files(){

	var uploadfiles = document.getElementById('fileinput');
	
	
	if(uploadfiles.files.length>0){	
	//var files = this.files;
		//alert(uploadfiles.files.length);
		var einheit_id_foto = document.getElementById('einheit_id_foto').value;
		for(var i=0; i<uploadfiles.files.length; i++){
			//var funct = uploadFile(uploadfiles.files[i], einheit_id_foto);
			uploadFile(uploadfiles.files[i], einheit_id_foto);
		}
				
		//window.location.href='?daten=leerstand&option=fotos_upload&einheit_id='+einheit_id_foto;
		
		
		
	}else{
		alert('Fotos wählen');
	}
	
	}

/*Bilder in vorschau einlesen*/
function previewImage(file) {
    var galleryId = "gallery";

    var gallery = document.getElementById(galleryId);
    var imageType = /image.*/;

    if (!file.type.match(imageType)) {
        throw "File Type must be an image";
    }

    var thumb = document.createElement("div");
    thumb.classList.add('thumbnail'); // Add the class thumbnail to the created div

    var img = document.createElement("img");
    img.file = file;
    thumb.appendChild(img);
    gallery.appendChild(thumb);

    // Using FileReader to display the image content
    var reader = new FileReader();
    reader.onload = (function(aImg) { return function(e) { aImg.src = e.target.result; }; })(img);
    reader.readAsDataURL(file);
}


function uploadFile(file, einheit_id){
    var url = '?daten=leerstand&option=foto_send_ajax';
    var xhr = new XMLHttpRequest();
    var fd = new FormData();
    xhr.open("POST", url, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            // Every thing ok, file uploaded
          //  console.log(xhr.responseText); // handle response.
       	  alert(xhr.responseText);
        	/*if(xhr.responseText=='OK'){
        		return true;
        	}else{
        		return false;
        	}*/
       	//reload_me();
        }
    };
    fd.append("upload_file", file);
    fd.append("einheit_id_foto", einheit_id);
    xhr.send(fd);
    
}

function del_file(filename){
	 var url = '?daten=leerstand&option=foto_loeschen';
	    var xhr = new XMLHttpRequest();
	    var fd = new FormData();
	    xhr.open("POST", url, true);
	    xhr.onreadystatechange = function() {
	        if (xhr.readyState == 4 && xhr.status == 200) {
	            // Every thing ok, file uploaded
	          //  console.log(xhr.responseText); // handle response.
	       	  alert(xhr.responseText);
	        	/*if(xhr.responseText=='OK'){
	        		return true;
	        	}else{
	        		return false;
	        	}*/
	        }
	    };
	    fd.append("filename", filename);
	 
	    xhr.send(fd);
}
