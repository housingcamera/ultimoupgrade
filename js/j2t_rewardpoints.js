function refreshJ2tDateAdminButons(custom_display_date_format){
    $$('.j2t-buttons-date-admin').each(function(el) {
        if ($(el).id == ''){ 
            var random_id = "J2T_"+Math.floor(Math.random()*10000);
            $(el).previous().id = random_id;
            $(el).id = random_id+"_trig";
            var calendarSetupObject = { inputField  : random_id, ifFormat : custom_display_date_format, showsTime : false, button : $(el).id, align : "Bl", singleClick : true };
            Calendar.setup(calendarSetupObject);
        }
    });
}
