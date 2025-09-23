<!-- Panel de resumen general de alumnos con discapacidad -->
<div id="barreras-aprendizaje" class="matricula-panel animate-fade delay-6">
    <div class="matricula-header">
        <h3 class="matricula-title"><i class="fas fa-wheelchair"></i> Resumen General de Alumnos con
            Barreras de Aprendizaje (datos demo)</h3>
    </div>
    <div class="matricula-body">
        <div class="stats-row">
            <?php
            // Calcular totales de alumnos con discapacidad
            $totalHombresDiscapacidad = 0;
            $totalMujeresDiscapacidad = 0;
            $totalGeneralDiscapacidad = 0;
            foreach ($alumnosDiscapacidad as $fila) {
                $totalHombresDiscapacidad += $fila['hombres'];
                $totalMujeresDiscapacidad += $fila['mujeres'];
                $totalGeneralDiscapacidad += $fila['total'];
            }
            ?>
            <div class="stat-box total-general">
                <div class="stat-value"><?php echo number_format($totalGeneralDiscapacidad); ?></div>
                <div class="stat-label">Total con Discapacidad</div>
                <div class="stat-icon"><i class="fas fa-wheelchair"></i></div>
            </div>
            <div class="stat-box sector-hombres">
                <div class="stat-value"><?php echo number_format($totalHombresDiscapacidad); ?></div>
                <div class="stat-label">Hombres</div>
                <div class="stat-percentage">
                    <?php echo $totalGeneralDiscapacidad > 0 ? round(($totalHombresDiscapacidad / $totalGeneralDiscapacidad) * 100, 1) : 0; ?>%
                </div>
                <div class="stat-icon"><i class="fas fa-mars"></i></div>
            </div>
            <div class="stat-box sector-mujeres">
                <div class="stat-value"><?php echo number_format($totalMujeresDiscapacidad); ?></div>
                <div class="stat-label">Mujeres</div>
                <div class="stat-percentage">
                    <?php echo $totalGeneralDiscapacidad > 0 ? round(($totalMujeresDiscapacidad / $totalGeneralDiscapacidad) * 100, 1) : 0; ?>%
                </div>
                <div class="stat-icon"><i class="fas fa-venus"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Panel de tabla detallada de alumnos con discapacidad por género -->
<div class="matricula-panel animate-fade delay-7 matricula-genero">
    <div class="matricula-header">
        <h3 class="matricula-title"><i class="fas fa-wheelchair"></i> Alumnos con Barreras de Aprendizaje por
            Nivel
            Educativo (datos demo)</h3>
    </div>
    <div class="matricula-body">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nivel Educativo</th>
                        <th>Total</th>
                        <th><i class="fas fa-mars"></i> Hombres</th>
                        <th>% Hombres</th>
                        <th><i class="fas fa-venus"></i> Mujeres</th>
                        <th>% Mujeres</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totalHombresDiscapacidad = 0;
                    $totalMujeresDiscapacidad = 0;
                    $totalGeneralDiscapacidad = 0;
                    foreach ($alumnosDiscapacidad as $fila):
                        $totalHombresDiscapacidad += $fila['hombres'];
                        $totalMujeresDiscapacidad += $fila['mujeres'];
                        $totalGeneralDiscapacidad += $fila['total'];
                        $porcH = $fila['total'] > 0 ? round(($fila['hombres'] / $fila['total']) * 100, 1) : 0;
                        $porcM = $fila['total'] > 0 ? round(($fila['mujeres'] / $fila['total']) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($fila['titulo_fila']); ?></td>
                            <td><?php echo number_format($fila['total']); ?></td>
                            <td class="col-hombres"><?php echo number_format($fila['hombres']); ?></td>
                            <td><?php echo $porcH; ?>%</td>
                            <td class="col-mujeres"><?php echo number_format($fila['mujeres']); ?></td>
                            <td><?php echo $porcM; ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td><strong>TOTAL GENERAL</strong></td>
                        <td><strong><?php echo number_format($totalGeneralDiscapacidad); ?></strong></td>
                        <td class="col-hombres">
                            <strong><?php echo number_format($totalHombresDiscapacidad); ?></strong>
                        </td>
                        <td><strong><?php echo $totalGeneralDiscapacidad > 0 ? round(($totalHombresDiscapacidad / $totalGeneralDiscapacidad) * 100, 1) : 0; ?>%</strong>
                        </td>
                        <td class="col-mujeres">
                            <strong><?php echo number_format($totalMujeresDiscapacidad); ?></strong>
                        </td>
                        <td><strong><?php echo $totalGeneralDiscapacidad > 0 ? round(($totalMujeresDiscapacidad / $totalGeneralDiscapacidad) * 100, 1) : 0; ?>%</strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Panel de análisis de tendencias de discapacidad por nivel educativo -->
<div class="matricula-panel animate-fade delay-8 panel-nivelaislado panel-discapacidad">
    <div class="header-nivelaislado">
        <h3 class="title-nivelaislado"><i class="fas fa-wheelchair"></i> Análisis de Barreras de Aprendizaje por
            Nivel
            Educativo (datos demo)</h3>
    </div>
    <div class="body-nivelaislado">
        <div id="cards-view-discapacidad" class="grid-nivelaislado">
            <?php foreach ($alumnosDiscapacidad as $fila): ?>
                <?php
                $porcH = $fila['total'] > 0 ? round(($fila['hombres'] / $fila['total']) * 100, 1) : 0;
                $porcM = $fila['total'] > 0 ? round(($fila['mujeres'] / $fila['total']) * 100, 1) : 0;
                $dominante = $fila['hombres'] > $fila['mujeres'] ? 'hombres' : 'mujeres';
                $participacion = $totalGeneralDiscapacidad > 0 ? round(($fila['total'] / $totalGeneralDiscapacidad) * 100, 1) : 0;
                ?>
                <div class="card-nivelaislado">
                    <div class="header-card-nivelaislado">
                        <h4><?php echo htmlspecialchars($fila['titulo_fila']); ?></h4>
                        <span class="participacion-nivelaislado" style="color: var(--accent-orange);">
                            <?php echo $participacion; ?>% del total con barreras del aprendizaje
                        </span>
                    </div>
                    <div class="content-nivelaislado">
                        <div class="sectorinfo-nivelaislado">
                            <div class="sectordom-nivelaislado <?php echo $dominante; ?>" style="font-weight:bold;">
                                <span class="sectorlabel-nivelaislado">Género dominante:</span>
                                <span class="sectorvalue-nivelaislado"
                                    style="color:<?php echo $dominante == 'hombres' ? '#5b8df6' : '#f472b6'; ?>;">
                                    <?php echo ucfirst($dominante); ?>
                                </span>
                            </div>
                            <div class="sectorstats-nivelaislado">
                                <div class="statmini-nivelaislado">
                                    <span class="valuenivelaislado" style="color:#5b8df6;">
                                        <?php echo number_format($fila['hombres']); ?>
                                    </span>
                                    <span class="labelnivelaislado">Hombres</span>
                                </div>
                                <div class="statmini-nivelaislado">
                                    <span class="valuenivelaislado" style="color:#f472b6;">
                                        <?php echo number_format($fila['mujeres']); ?>
                                    </span>
                                    <span class="labelnivelaislado">Mujeres</span>
                                </div>
                            </div>
                        </div>
                        <div class="progressbar-nivelaislado"
                            style="height: 18px; background: #ede9fe; border-radius: 8px; overflow: hidden;">
                            <div class="progresspublico-nivelaislado"
                                style="width: <?php echo $porcH; ?>%; background: #5b8df6; height: 100%; float:left;">
                            </div>
                            <div class="progressprivado-nivelaislado"
                                style="width: <?php echo $porcM; ?>%; background: #f472b6; height: 100%; float:left;">
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>