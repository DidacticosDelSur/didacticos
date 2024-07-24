
var $croppie = new Croppie($('#croppie-field')[0], {
    enableExif: true,
    enableResize:true,
    enableZoom:true,
    boundary: { width: 400, height: 400 },
    viewport: {
        height: 300,
        width: 300
    },
    enableOrientation: true
})
$(document).ready(function(){
    var img_name;
    // console.log($croppie)
    $('#upload').on('change', function(e){
        var reader = new FileReader();
          img_name = e.target.files[0].name;
        reader.onload = function (e) {
            $croppie.bind({
                url: e.target.result
            });
            $('#croppie-editor').removeClass('d-none')
        }
        reader.readAsDataURL(this.files[0]);
    })
   
    
    $('#rotate-left').click(function(){
        $croppie.rotate(90);
    })
    $('#rotate-right').click(function(){
        $croppie.rotate(-90);

    })
    $('#upload-btn').click(function(){
        $croppie.result({
            type:'base64',
            format: 'png'
        }).then((imgBase64)=>{
           $.ajax({
            url:'save_image.php',
            method:'POST',
            data: { 'img' : imgBase64, 'fname' : img_name },
            dataType: 'json',
            error: err => {
                console.error(err)
            },
            success: function(response){
                if(response.status == 'success'){
                    alert("Cropped Image has been saved successfully.")
                    location.reload()
                }else{
                    console.error(response)
                }
            }
           })
        })
    })
})