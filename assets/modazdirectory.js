document.addEventListener("DOMContentLoaded",function(){azTriggerSelect(),azTriggerClick(),azModal(),azPagination()});var azPagination=function(){for(var e=document.querySelectorAll(".modazdirectory__pagination a"),t=0;t<e.length;t++)e[t].addEventListener("click",function(e){e.preventDefault();var t=new URLSearchParams(e.target.href),a="All";t.get("lastletter")&&(a=t.get("lastletter"));var o=t.get("start");azRequest(a,o),document.getElementById("modazdirectory").scrollIntoView()})},azPush=function(e){if(window.history&&history.pushState){var t="?lastletter="+e+"#modazdirectory";history.pushState(e,"",t)}},azTriggerClick=function(){for(var e=document.querySelectorAll(".modazdirectory__link"),t=0;t<e.length;t++)e[t].addEventListener("click",function(e){e.preventDefault();var t=this.rel;azRequest(t),azPush(t)})},azTriggerSelect=function(){document.getElementById("modazdirectory__select").addEventListener("change",function(e){var t,a=e.target.selectedIndex,o=e.target.options;t=0==o[a].index?o[1].text:o[a].text,azRequest(t),azPush(t)})},azSelectDefault=function(e){for(var t=document.getElementById("modazdirectory__select"),a=0;a<t.options.length;a++)t.options[a].text==e&&(t.options[a].selected=!0)};window.addEventListener("popstate",function(e){null!==e.state&&azRequest(e.state)});var azRequest=function(e,t){t=t||"0","All"==e&&(e=Joomla.JText._("JALL"));var a="?option=com_ajax&module=azdirectory&method=getContacts&data[letter]="+e+"&data[start]="+t+"&data[title]="+modazModuleTitle+"&format=json";fetch(a).then(function(e){return e.ok?e.text():Promise.reject(e)}).then(function(t){document.getElementById("modazdirectory").innerHTML=t,azSelectDefault(e)}).then(azEmailCloak).then(azTriggerSelect).then(azTriggerClick).then(azModal).then(azPagination).catch(function(e){console.warn(e)})},azEmailCloak=function(){for(var e,t,a=document.getElementById("modazdirectory").querySelectorAll("script"),o=0;o<a.length;o++)e=a[o],(t=document.createElement("script")).type=e.type,e.innerHTML?t.innerHTML=e.innerHTML:t.src=e.src,t.async=!1,e.replaceWith(t)},azModal=function(){if(0!=modazNameHyperlink){var e=modazModalStyle.displayFormat;"plain"!=e&&"tabs"!=e||jQuery('.modazdirectory__results a[data-toggle="modal"]').on("click",function(e){var t=jQuery(e.currentTarget).data("remote");return jQuery("#modazdirectory__modal").modal("show").find("#modazdirectory__modal-body").load(t),!1}),"sliders"==e&&jQuery('.modazdirectory__results a[data-toggle="modal"]').on("click",function(e){var t=jQuery(e.currentTarget).data("remote");return jQuery("#modazdirectory__modal").on("shown.bs.modal",function(){jQuery("#modazdirectory__modal-body").load(t,function(){jQuery("#slide-contact").attr("id","modazdirectory__slide-contact"),jQuery("#modazdirectory__modal .accordion-toggle").each(function(){jQuery(this).on("click",function(){var e=jQuery(this).parent().parent().next();return e.hasClass("in")?e.removeClass("in"):e.addClass("in"),!1})})})}).modal(),!1}),jQuery("[data-dismiss]").on("click",function(){jQuery("#modazdirectory__modal-body").html('<div class="modal-spinner"></div>')})}};