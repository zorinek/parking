$(function(){

});

$(document).ready(function() {
    $(".show-password").on('click', function(event) {
        event.preventDefault();
        if(event.target.previousSibling.previousSibling.type == "text")
        {
            event.target.previousSibling.previousSibling.type = "password";
            event.target.classList.add( "fa-eye-slash" );
            event.target.classList.remove( "fa-eye" );
        }else if(event.target.previousSibling.previousSibling.type == "password"){
            event.target.previousSibling.previousSibling.type = "text";
            event.target.classList.remove( "fa-eye-slash" );
            event.target.classList.add( "fa-eye" );
        }
    });
});


var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
  return new bootstrap.Popover(popoverTriggerEl, {

        sanitize: false,


})
});

$('body').on('click', function (e) {
    $('[data-bs-toggle=popover]').each(function () {
        // hide any open popovers when the anywhere else in the body is clicked
        if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
            $(this).popover('hide');
        }

        if(this.id == e.target.getAttribute("data-value"))
        {
            $(this).popover('hide');
        }
    });
});

function showErrors(errors, focus)
{
	errors.forEach(function(error) {
		if (error.message) {
//			$(error.element).closest('tr').addClass('has-error').find('.error').remove();
//			$('<span class=error>').text(error.message).insertAfter(error.element);
//                        console.log($(error.element).parent().closest("div"));
                        $(error.element).parent().find(".invalid-feedback").text(error.message);
                        $(error.element).removeClass("is-valid");
                        $(error.element).addClass("is-invalid");
		}


		if (focus && error.element.focus) {
			error.element.focus();
			focus = false;
		}
	});
}

function removeErrors(elem)
{
	if ($(elem).is('form')) {
		$('.has-error', elem).removeClass('has-error');
		$('.error', elem).remove();
//                console.log("ELELEEL", elem);
//                $(elem).removeClass("is-invalid");
//                $(elem).addClass("is-valid");
	} else {
//		$(elem).closest('tr').removeClass('has-error').find('.error').remove();
//                console.log("WLW", elem);
                $(elem).removeClass("is-invalid");
                $(elem).addClass("is-valid");
	}
}

Nette.showFormErrors = function(form, errors) {
	removeErrors(form);
	showErrors(errors, true);
};

$(function() {
	$(':input').keyup(function() {
		if (this.getAttribute("novalidate") === null) {
                    removeErrors(this);
                    Nette.formErrors = [];
                    Nette.validateControl(this);
                    showErrors(Nette.formErrors);
                }
	});
        
	$(':input').change(function() {
                if (this.getAttribute("novalidate") === null) {
                    removeErrors(this);
                    Nette.formErrors = [];
                    Nette.validateControl(this);
                    showErrors(Nette.formErrors);
                }
	});

	$(':input').blur(function() {
            if (this.getAttribute("novalidate") === null) {
                Nette.formErrors = [];
                Nette.validateControl(this);
                showErrors(Nette.formErrors);
            }
	});
});

Nette.validators.AppFormsCustomFormRules_validateCustomPassword = function (elem, arg, val) {

        var pass = true;
        var numbers = /[0-9]/;
        var special_chars = /[ `!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~]/;
        
        if(arg > 0 && val.toUpperCase() === val)
        {
            pass = false;
        }
        
        if(arg > 0 && !numbers.test(val))
        {
            pass = false;
        }

        if(arg > 1 && val.toLowerCase() === val)
        {
            pass = false;
        }

        if(arg > 2 && !special_chars.test(val))
        {
            pass = false;
        }
        return pass;
};

$(document).ready(function(){   
    setTimeout(function () {
        $("#cookieBar").fadeIn(200);
     }, 4000);
    $(".cookieBarButton").click(function() {
        var date = new Date();
                date.setFullYear(date.getFullYear() + 10);
                document.cookie = 'cookie-bar=1; path=/; expires=' + date.toGMTString();
        $("#cookieBar").fadeOut(200);
    }); 
    $("#closeCookieBar").click(function() {
        var date = new Date();
                date.setFullYear(date.getFullYear() + 10);

        $("#cookieBar").fadeOut(200);
    }); 
}); 

function setReply(id)
{
    document.getElementById("scroll_to").scrollIntoView();
    event.preventDefault();
    document.getElementById("reply").value = id;
    
    document.getElementById("reply_to").classList.remove("d-none");
    document.getElementById("reply_to_value").value = "#" + id;

}

function removeReply()
{
    document.getElementById("reply").value = "";
    document.getElementById("reply_to").classList.add("d-none");
}
