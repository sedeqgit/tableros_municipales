<HTML>

<HEAD>
    <LINK REL="stylesheet" HREF="../css/estadistica.css">
</HEAD>

<BODY>
    <?php
    function titulo_conteo()
    {
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>PÚBLICAS</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>PRIVADAS</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>TOTAL</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>%PÚBLICAS</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>%PRIVADAS</TD>";
    }
    function titulo_hm()
    {
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>PÚBLICAS</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>PRIVADAS</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>TOTAL</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>%PÚBLICAS</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>%PRIVADAS</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>H</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>M</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>TOTAL</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>%H</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>%M</TD>";
    }
    function titulos_estado()
    {

        echo "<BR><TABLE BORDER='1' BORDER='1' width='100%' CLASS='tb_borde'>";
        echo "<TR>";
        echo "<TD ROWSPAN=2 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>CICLO</TD>";
        echo "<TD ROWSPAN=2 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>TIPO EDUCATIVO</TD>";
        echo "<TD ROWSPAN=2 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>SUBNIVEL</TD>";
        echo "<TD ROWSPAN=2 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>MUNICIPIO</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>ESCUELAS</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>ALUMNOS</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>ALUMNOS</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>DOCENTES</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>DOCENTES</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>GRUPOS</TD>";
        echo "<TD COLSPAN=3 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>AULAS</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>DISCAPACIDAD</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>DISCAPACIDAD</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>HL</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>HL</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>PRIMERO</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>PRIMERO</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>SEGUNDO</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>SEGUNDO</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>TERCERO</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>TERCERO</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>EGRESADOS</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>EGRESADOS</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>APOYO USAER</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>APOYO USAER</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>NAC EXT</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>NAC EXT</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>MISMA ENT</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>MISMA ENT</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>OTRA ENT</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>OTRA ENT</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>OTRO PAIS</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>OTRO PAIS</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>ORIG TOTAL</TD>";
        echo "<TD COLSPAN=5 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>ORIG TOTAL</TD>";
        echo "</TR>";

        echo "<TR>";
        //ESCUELAS
        titulo_conteo();
        //ALUMNOS
        titulo_hm();
        //DOCENTES				
        titulo_hm();
        //GRUPOS
        titulo_conteo();
        //AULAS
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>EXISTENTES</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>EN USO</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>ADAPTADAS</TD>";
        //DISCAPACIDAD
        titulo_hm();
        //HABLANTES L
        titulo_hm();
        //PRIMER GRADO
        titulo_hm();
        //SEGUNDO GRADO
        titulo_hm();
        //TERCER GRADO
        titulo_hm();
        //EGRESADOS
        titulo_hm();
        //APOYO USAER
        titulo_hm();
        //NACIDOS EXTRANJERO
        titulo_hm();
        //ORIGEN MISMA ENTIDAD 
        titulo_hm();
        //ORIGEN OTRA ENTIDAD 
        titulo_hm();
        //ORIGEN OTRA PAIS 
        titulo_hm();
        //ORIGEN OTRO TOTAL 
        titulo_hm();
        echo "</TR>";
    }

    function titulos_usbq()
    {

        echo "<BR><TABLE BORDER=1 BORDER='1' width='100%' CLASS='tb_borde'>";
        echo "<TR>";
        echo "<TD COLSPAN=32 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>DATOS USEBEQ</TD>";
        echo "</TR>";
        echo "<TR>";
        echo "<TD ROWSPAN=2 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>CICLO</TD>";
        echo "<TD ROWSPAN=2 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>TIPO EDUCATIVO</TD>";
        echo "<TD ROWSPAN=2 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>SUBNIVEL</TD>";
        echo "<TD ROWSPAN=2 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>MUNICIPIO</TD>";
        echo "<TD ROWSPAN=2 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>SERVICIO</TD>";
        echo "<TD COLSPAN=3 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>ESCUELAS</TD>";
        echo "<TD COLSPAN=3 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>ALUMNOS</TD>";
        echo "<TD COLSPAN=3 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>ALUMNOS</TD>";
        echo "<TD COLSPAN=3 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>DOCENTES</TD>";
        echo "<TD COLSPAN=3 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>DOCENTES</TD>";
        echo "<TD COLSPAN=3 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>GRUPOS</TD>";
        echo "<TD COLSPAN=3 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>AULAS</TD>";
        echo "<TD COLSPAN=3 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>DISCAPACIDAD</TD>";
        echo "<TD COLSPAN=3 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>DISCAPACIDAD</TD>";
        echo "<TD COLSPAN=3 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>HL</TD>";
        echo "<TD COLSPAN=3 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>HL</TD>";
        echo "<TD COLSPAN=3 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>NAC EXT</TD>";
        echo "<TD COLSPAN=3 CLASS='titu_tabla' ALIGN='CENTER' VALIGN='CENTER'>NAC EXT</TD>";
        echo "<TR>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>PÚBLICAS</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>PRIVADAS</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>TOTAL</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>PÚBLICAS</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>PRIVADAS</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>TOTAL</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>H</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>M</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>TOTAL</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>PÚBLICAS</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>PRIVADAS</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>TOTAL</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>H</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>M</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>TOTAL</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>PÚBLICAS</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>PRIVADAS</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>TOTAL</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>EXISTENTES</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>EN USO</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>ADAPTADAS</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>PÚBLICAS</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>PRIVADAS</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>TOTAL</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>H</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>M</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>TOTAL</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>PÚBLICAS</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>PRIVADAS</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>TOTAL</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>H</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>M</TD>";
        echo "<TD CLASS='subtitu_tabla' ALIGN='CENTER' VALIGN='CENTER'>TOTAL</TD>";
        echo "</TR>";
    }

    ?>
</BODY>

</HTML>