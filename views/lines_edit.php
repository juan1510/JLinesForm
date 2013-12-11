<?php
    /*
     * Lineas Renderizadas para modo edit segun opciones del Widget
     */
     /* @var $this JLinesForm */
?>
<table class="templateFrame <?php echo $this->tableClass; ?>" cellspacing="0">
       <?php $this->renderHeaders();?>
      <tbody class="templateTarget">
             <?php if($this->showElementsSaved)$this->renderElementsSaved(); ?>
             <?php echo CHtml::hiddenField(get_class($this->model).'_delete','' ); ?>
      </tbody>
      <tfoot>
             <tr>
                 <td colspan='<?php echo $this->getCountColspan();?>'>
                        <?php echo $this->getButtonAddLine();?>
                        <div id="widgets" style="display: none">
                            <?php echo $this->renderElementsWidgets();?>
                        </div>
                        <textarea class="template" style="display:none">
                            <tr class="templateContent">
                                <?php $this->renderElementsTemplate();?>
                            </tr>
                        </textarea>
                 </td>
            </tr>
      </tfoot>
</table>
