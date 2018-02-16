<?php

require "base.php";
global $_base;

$_base->page->add_title("Lotto", "Trekninger");
kf_menu::$data['lotto'] = true;

echo '
<div class="bg1_c xsmall">
	<h1 class="bg1">Lotto: Siste trekninger<span class="left"></span><span class="right"></span></h1>
	<p class="h_left"><a href="lotto">&laquo; Tilbake</a></p>
	<div class="bg1">';


// hent trekningene på nåværende side
$pagei = new pagei(pagei::ACTIVE_GET, "side", pagei::PER_PAGE, 12);
$result = $pagei->query("SELECT CEILING((time-900)/1800)*1800+900 FROM lotto_vinnere WHERE time > ".(time()-259200)." GROUP BY CEILING((time-900)/1800)*1800+900 ORDER BY time DESC");

if ($result->rowCount() == 0)
{
	echo '
		<p class="c">Ingen trekninger har blitt utført.</p>';
}

else
{
	$row = $result->fetch(\PDO::FETCH_NUM);
	$last = $row[0];
	do {
		$first = $row[0] - 1800;
	} while ($row = $result->fetch(\PDO::FETCH_NUM));
	
	echo '
		<p class="c">'.$_base->date->get($first+1800)->format().' til '.$_base->date->get($last)->format().'</p>';
	
	// hent vinnerene
	$result = \Kofradia\DB::get()->query("SELECT lv_up_id, time, won, total_lodd, total_users, type FROM lotto_vinnere WHERE time >= $first AND time < $last ORDER BY type");
	$rounds = array();
	
	// legg i riktig gruppe
	while ($row = $result->fetch())
	{
		$end = ceil(($row['time']-900)/1800)*1800 + 900;
		
		if (!isset($rounds[$end]))
		{
			$rounds[$end] = array(
				"time" => $end,
				"total_lodd" => $row['total_lodd'],
				"total_users" => $row['total_users'],
				"users" => array()
			);
		}
		
		$rounds[$end]['users'][$row['type']] = array($row['lv_up_id'], $row['won']);
	}
	krsort($rounds);
	
	foreach ($rounds as $round)
	{
		echo '
		<div class="section">
			<h2>'.$_base->date->get($round['time'])->format().'</h2>
			<p class="h_right">'.game::format_number($round['total_lodd']).' lodd, '.game::format_number($round['total_users']).' spiller'.($round['total_users'] == 1 ? '' : 'e').'</p>
			<dl class="dd_right">';
		
		foreach ($round['users'] as $num => $row)
		{
			echo '
				<dt>'.$num.' - <user id="'.$row[0].'" /></dt>
				<dd>'.game::format_cash($row[1]).'</dd>';
		}
		
		echo '
			</dl>
		</div>';
	}
	
	// vis side
	echo '
		<p class="c">'.$pagei->pagenumbers().'</p>';
}

echo '
	</div>
</div>';

$_base->page->load();