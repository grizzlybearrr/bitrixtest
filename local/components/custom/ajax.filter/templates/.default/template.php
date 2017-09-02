<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();?>

<?=GetMessage("PREFORM_NOTE");?>

<div class="b-filter js-filter">
  <form class="b-filter__param-list" name="filter_form">

    <div class="b-filter__param">
      <label for="id-qualif" class="b-filter__param-name">
        <?=GetMessage("QUALIFICATION_LABEL");?>
      </label>

      <select class="b-filter__param-value js-filter__param-value"  id="id-qualif" name="qualif" multiple>
      	<?foreach($arResult['qualif_list'] as $qualif):?>
	        <option value="<?=$qualif['id']?>">
	        	<?=$qualif['name']?>
	        </option>
	    <?endforeach;?>
      </select>
    </div>

    <div class="b-filter__param">
      <label for="id-city" class="b-filter__param-name">
        <?=GetMessage("CITY_LABEL");?>
      </label>

      <select class="b-filter__param-value js-filter__param-value" id="id-city" name="city" multiple>
        <?foreach($arResult['city_list'] as $city):?>
	        <option value="<?=$city['id']?>">
	        	<?=$city['name']?>
	        </option>
	    <?endforeach;?>
      </select>
    </div>

  </form>

  <div class="b-filter__result">

    <table width="100%" border="1">
      <thead>
        <tr>
          <th><?=GetMessage('THEAD_USER_NAME')?></th>
          <th><?=GetMessage('THEAD_USER_QUALIFICATION')?></th>
          <th><?=GetMessage('THEAD_USER_CITY')?></th>
        </tr>
      </thead>
      <tbody class="js-filter__result">

      	<?foreach($arResult['user_list'] as $user):?>
	        <tr>
	          <td><?=$user['name']?></td>
	          <td><?=$user['qualification']?></td>
	          <td><?=$user['city']?></td>
	        </tr>
        <?endforeach;?>

      </tbody>
    </table>
  </div>
</div>

<!-- end component -->