<?php
    /*
     * Lineas Renderizadas para modo edit segun opciones del Widget
     */
     /* @var $this JLinesForm */

    echo $this->form->errorSummary($this->model); 
?>
<table class="templateFrame table table-bordered table table-hover table table-striped" cellspacing="0">
       <?php $this->renderHeaders();?>
      <tbody class="templateTarget">
                  <?php if(!$this->model->isNewRecord) :?>
                            <?php foreach($modelLinea as $i=>$linea): ?>
                                    <tr class="templateContent">
                                        <td>
                                                <?php echo '<span id="lineaU_'.$i.'">'.$linea->LINEA_NUM.'</span>'; ?>
                                       </td>
                                       <td>
                                                <?php echo '<span id="articuloU_'.$i.'">'.$linea->ARTICULO.'</span>'; ?>
                                                <?php echo CHtml::activeHiddenField($linea,"[$i]ARTICULO"); ?>
                                       </td>
                                       <td> 
                                                <?php echo '<span id="descripcionU_'.$i.'">'.$linea->DESCRIPCION.'</span>'; ?>
                                                <?php echo CHtml::activeHiddenField($linea,"[$i]DESCRIPCION"); ?>
                                       </td>
                                       <td>
                                                <?php echo '<span id="unidadU_'.$i.'">'.$linea->UNIDAD.'</span>'; ?>
                                                <?php echo CHtml::activeHiddenField($linea,"[$i]UNIDAD"); ?>
                                        </td>
                                       <td>
                                                <?php echo '<span id="tipo_precioU_'.$i.'">'.$linea->TIPO_PRECIO.'</span>'; ?>
                                                <?php echo CHtml::activeHiddenField($linea,"[$i]TIPO_PRECIO"); ?>
                                        </td>
                                        <td>
                                                <?php echo '<span id="precio_unitarioU_'.$i.'">'.$linea->PRECIO_UNITARIO.'</span>'; ?>
                                                <?php echo CHtml::activeHiddenField($linea,"[$i]PRECIO_UNITARIO"); ?>                                        
                                        </td>                                
                                        <td>
                                                <?php echo '<span id="porc_descuentoU_'.$i.'">'.$linea->PORC_DESCUENTO.'</span>'; ?>
                                                <?php echo CHtml::activeHiddenField($linea,"[$i]PORC_DESCUENTO"); ?>                                        
                                        </td>
                                        <td>
                                                <?php echo '<span id="monto_descuentoU_'.$i.'">'.$linea->MONTO_DESCUENTO.'</span>'; ?>
                                                <?php echo CHtml::activeHiddenField($linea,"[$i]PORC_DESCUENTO"); ?>                                        
                                        </td>
                                        <td>
                                                <?php echo '<span id="porc_impuestoU_'.$i.'">'.$linea->PORC_IMPUESTO.'</span>'; ?>
                                                <?php echo CHtml::activeHiddenField($linea,"[$i]PORC_DESCUENTO"); ?>                                        
                                        </td>
                                        <td>
                                                <?php echo '<span id="valor_impuestoU_'.$i.'">'.$linea->VALOR_IMPUESTO.'</span>'; ?>
                                                <?php echo CHtml::activeHiddenField($linea,"[$i]PORC_DESCUENTO"); ?>                                        
                                        </td>
                                        <td>
                                                <?php echo '<span id="estadoU_'.$i.'">'.$linea->ESTADO.'</span>'; ?>
                                                <?php echo CHtml::activeHiddenField($linea,"[$i]ESTADO"); ?>                                        
                                        </td>
                                        <td>
                                                <?php echo '<span id="comentarioU_'.$i.'">'.$linea->COMENTARIO.'</span>'; ?>
                                                <?php echo CHtml::activeHiddenField($linea,"[$i]COMENTARIO"); ?>                                        
                                        </td>
                                        <td>
                                                <?php echo '<span id="totalU_'.$i.'">'.$linea->ESTADO.'</span>'; ?>
                                        </td>
                                        <td style="width: 50px;">                                     
                                               <div class="remove" id ="remover" style="float: left; margin-left: 5px;">
                                                          <?php $this->widget('bootstrap.widgets.TbButton', array(
                                                                         'buttonType'=>'button',
                                                                         'type'=>'danger',
                                                                         'size'=>'mini',
                                                                         'icon'=>'minus white',
                                                                         'htmlOptions'=>array('id'=>'btn-remover','class'=>'eliminaRegistro','name'=>$i,'disabled'=>$model->ESTADO == 'P' ? false : true)

                                                                 ));
                                                                 $this->numLines = ++$i;
                                                         ?>
                                               </div>
                                           </td>
                                 </tr>
                           <?php  endforeach; ?>
                           <?php echo CHtml::hiddenField('eliminar','' ); ?>
                  <?php endif; ?>
      </tbody>
      <tfoot>
             <tr>
                 <td colspan='<?php echo $this->getCountColspan();?>'>
                        <div id='<?php echo $this->_idAdd;?>' class="add"></div>
                        <?php echo $this->getButtonAddLine();?>
                        <textarea class="template" style="display:none">
                                  <tr class="templateContent"><?php $this->renderElementsTemplate();?></tr>
                        </textarea>
                 </td>
            </tr>
      </tfoot>
</table>
