$(document).ready(function () {
    let lastUpdate = Math.floor(Date.now() / 1000);
    let activeUser = null;
    let selectedProduct = null;
    let activeStation = $('#mainText').data()['station'];
    let progressBar = new ProgressBar.Circle('#progressbar', {
        color: 'blue',
        strokeWidth: 10,
        trailWidth: 10,
        duration: 30000,
        svgStyle: {
            transform: 'scaleX(-1)',
        }

    });
    $('#progressbar').hide();
    $('.productCard').width()

    function checkUpdates() {
        $.ajax({
                url: 'https://snackwerke.ddev.site/rest/get_data',
                dataType: 'json',
                success: function (response) {
                    $('#testing').text(activeUser)
                    if (response.last_modified > lastUpdate) {
                        let station = window.location.href.split('/station/')[1]
                        if (response.station === station) {
                            lastUpdate = response.last_modified;
                            if (response.name !== "") {

                                if (response.id !== activeUser) {
                                    $('#nameText').animate({top: '-20%'}, 25).animate({top: '0%'}, 500);
                                    $('.collective_purchase_number').text(0)
                                    $('.collective_purchase').attr('disabled','true')
                                    $('input').val(0);
                                }
                                activeUser = response.id;
                                $('#mainText').fadeOut(250);
                                $('#nameText').animate({top: '0%'}, 500).text("Hallo " + response.name)
                                progressBar.set(1);
                                progressBar.animate(0);
                                $('#progressbar').fadeIn(400);
                                $('.products').animate({top: '0'}, 500);
                                $('.collective_purchase').animate({bottom: '10px'}, 500)


                            } else {
                                activeUser = null;
                                $('#nameText').animate({top: '-20%'}, 500);
                                $('#mainText').fadeIn(250).animate({left: '47%'}, 100).animate({left: '53%'}, 100).animate({left: '47%'}, 100).animate({left: '50%'}, 100)
                                $('#progressbar').fadeOut(400);
                                $('.products').animate({top: '100vh'}, 500);
                                $('.collective_purchase').animate({bottom: '-50px'}, 500)
                                $('.collective_purchase_number').text(0)
                                $('.collective_purchase').attr('disabled','true')
                                $('input').val(0);
                            }
                        }
                    }
                    if (lastUpdate + 30 < Math.floor(Date.now() / 1000) && activeUser !== null) {
                        activeUser = null;
                        $('#mainText').delay(500).fadeIn(250);
                        $('#nameText').animate({top: '-20%'}, 500);
                        $('#progressbar').fadeOut(400);
                        $('.products').animate({top: '100vh'}, 500);
                        $('.collective_purchase').animate({bottom: '-50px'}, 500)
                        $('.collective_purchase_number').text(0)
                        $('.collective_purchase').attr('disabled','true')
                        $('input').val(0);

                    }

                },
                complete: function () {
                    // Schedule the next check in 0,1 second
                    setTimeout(checkUpdates, 100);
                }
            }
        )
        ;
    }

    //directly buy single product
    $('.direct_purchase').click(function () {
        selectedProduct = this.parentElement.dataset.product;

        let data = {};
        data[selectedProduct] = 1;

        $.ajax({
            url: "https://snackwerke.ddev.site/buy/" + activeStation + "/" + activeUser,
            data: data,
            type: 'post',
            success: function(data) {
                console.log(data['success'])
                if (data['success']) {
                    $(location).prop('href', "https://snackwerke.ddev.site/checkout/" + activeStation + "/" + activeUser + "/" + data['total'])
                } else if (data['message'] === 'swiper no swiping') {
                    $(location).prop('href', "https://snackwerke.ddev.site/shame");
                }
            }
        });
    });

    $('.change_amount').click(function () {
        let purchAllBtn=document.querySelector('.collective_purchase_number');
        if(this.classList.contains("add")){
            this.parentElement.querySelector("input").value++;
            purchAllBtn.innerHTML++;
            purchAllBtn.parentElement.disabled=false;

        }else{
            if(this.parentElement.querySelector("input").value>0){
                this.parentElement.querySelector("input").value--
                purchAllBtn.innerHTML--;
                if(purchAllBtn.innerHTML==="0"){
                    purchAllBtn.parentElement.disabled=true;
                }
            }
        }

    })

    $('.collective_purchase').click(function () {
       let selectedProducts=document.querySelectorAll('.productCard');
       let cart={};
       selectedProducts.forEach(function (product) {
           console.log(product)
           let prodName= product.dataset.product;
           let prodNumber=product.querySelector("input").value;
           cart[prodName]=prodNumber;
       })
        $.ajax({
            url: "https://snackwerke.ddev.site/buy/" + activeStation + "/" + activeUser,
            data: cart,
            type: 'post',
            success: function(data) {
                if (data['success']) {
                    $(location).prop('href', "https://snackwerke.ddev.site/checkout/" + activeStation + "/" + activeUser + "/" + data['total'] + "/" + data['number'])
                } else if (data['message'] === 'swiper no swiping') {
                    $(location).prop('href', "https://snackwerke.ddev.site/shame");
                }
            }
        });
    });
// Start the update loop
    checkUpdates();
})
;