<?
use \Bitrix\Main\Application;

class PricemultHistoryTable {
	function __construct() {
	}

	//получить историю наценок
	protected function getData() {
		$connection = Application::getConnection();
		$query_str = <<<QUERY
			SELECT * FROM pricemult_history ORDER BY time DESC;
QUERY;
		$response = $connection->query($query_str);
		return $response->fetchAll();
	}

	//отобразить историю наценок
	public function show( $title="" ) {
		$table = $this->getData();
		if(empty($table)) {
			return;
		}
		?>

		<div class="adm-detail-title">
			<?=$title?>
		</div>

		<table class="adm-list-table" id="tbl_sql">
			<thead>
				<tr class="adm-list-table-header">
					<?foreach( $table[0] as $key=>$value ):?>
						<td class="adm-list-table-cell">
							<div class="adm-list-table-cell-inner">
								<?=$key?>
							</div>
						</td>
					<?endforeach;?>
				</tr>
			</thead>
			<tbody>
				<?foreach( $table as $row ):?>
					<tr class="adm-list-table-row">
						<?foreach( $row as $key=>$value ):?>
							<?if($key=='time') $value = $value->toString()?>
							<td class="adm-list-table-cell">
								<?=$value?>
							</td>
						<?endforeach;?>
					</tr>
				<?endforeach;?>
			</tbody>
		</table>
		<?
	}

	//сделать пометку в журнале
	public function storeAction($action) {
		$connection = Application::getConnection();
		$iblockId = $action['IBLOCK_ID'];
		$sectionId = $action['SECTION_ID'];
		$percentVal = $action['PERCENT'];
		
		$query_str = <<<QUERY
			INSERT INTO pricemult_history 
			SET `iblock_id` = $iblockId, 
				`section_id` = $sectionId, 
				`percent` = $percentVal, 
				time = NOW();
QUERY;
		$response = $connection->query($query_str);
	}
}