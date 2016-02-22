{%config_load file="test.conf" section="setup"%}
{%include file="header.tpl" title=foo%}

{%include file=$data.tpl%}

{%include file="footer.tpl"%}
