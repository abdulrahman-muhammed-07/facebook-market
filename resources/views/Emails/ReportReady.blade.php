<p>Hello !!</p>

<p>Your exporting csv fle to facebook data are ready, please click <a
        href="{{ env('PLUGIN_DOMAIN_LINK').'/download?token=' .$token }}">here</a> to download</p>




@if ($error_link_report != null)
    <p>You can also download your error log report from <a href="{{ $error_link_report }}">here</a></p>
@endif





<p>Regards,</p>
