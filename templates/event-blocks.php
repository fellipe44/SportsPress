<?php
/**
 * Event Blocks
 *
 * @author 		ThemeBoy
 * @package 	SportsPress/Templates
 * @version     0.8
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$primary_result = get_option( 'sportspress_primary_result', null );

$defaults = array(
	'status' => 'default',
	'number' => -1,
	'paginated' => get_option( 'sportspress_calendar_paginated', 'yes' ) == 'yes' ? true : false,
	'rows' => get_option( 'sportspress_calendar_rows', 10 ),
	'order' => 'default',
	'show_all_events_link' => false,
);

extract( $defaults, EXTR_SKIP );

$calendar = new SP_Calendar( $id );
if ( $status != 'default' )
	$calendar->status = $status;
if ( $order != 'default' )
	$calendar->order = $order;
$data = $calendar->data();
$usecolumns = $calendar->columns;

if ( isset( $columns ) )
	$usecolumns = $columns;
?>
<div class="sp-table-wrapper">
	<table class="sp-event-blocks sp-data-table<?php if ( $paginated ) { ?> sp-paginated-table<?php } ?>" data-sp-rows="<?php echo $rows; ?>">
		<thead><tr><th></th></tr></thead> <?php # Required for DataTables ?>
		<tbody>
			<?php
			$i = 0;

			if ( is_int( $number ) && $number > 0 )
				$limit = $number;

			foreach ( $data as $event ):
				if ( isset( $limit ) && $i >= $limit ) continue;

				$results = get_post_meta( $event->ID, 'sp_results', true );

				$teams = get_post_meta( $event->ID, 'sp_team' );
				$logos = array();
				$main_results = array();

				$j = 0;
				foreach( $teams as $team ):
					if ( ! has_post_thumbnail ( $team ) )
						continue;
					$j++;
					$logo = get_the_post_thumbnail( $team, 'sportspress-fit-icon', array( 'class' => 'team-logo logo-' . ( $j % 2 ? 'odd' : 'even' ) ) );
					$logos[] = $logo;
					$team_results = sp_array_value( $results, $team, null );

					if ( $primary_result ):
						$team_result = sp_array_value( $team_results, $primary_result, null );
					else:
						if ( is_array( $team_results ) ):
							end( $team_results );
							$team_result = prev( $team_results );
						else:
							$team_result = null;
						endif;
					endif;
					if ( $team_result != null )
						$main_results[] = $team_result;

				endforeach;
				?>
				<tr class="sp-row sp-post<?php echo ( $i % 2 == 0 ? ' alternate' : '' ); ?>">
					<td>
						<?php echo implode( $logos, ' ' ); ?>
						<time class="event-date"><?php echo get_the_time( get_option( 'date_format' ), $event ); ?></time>
						<?php if ( $event->post_status == 'future' ): ?>
							<h5 class="event-time"><?php echo get_the_time( get_option( 'time_format' ), $event ); ?></h5>
						<?php else: ?>
							<h5 class="event-results"><?php echo implode( $main_results, ' - ' ); ?></h5>
						<?php endif; ?>
						<h3 class="event-title"><a href="<?php echo get_post_permalink( $event ); ?>"><?php echo $event->post_title; ?></a></h3>
					</td>
				</tr>
				<?
				$i++;
			endforeach;
			?>
		</tbody>
	</table>
	<?php
	if ( $id && $show_all_events_link )
		echo '<a class="sp-calendar-link sp-view-all-link" href="' . get_permalink( $id ) . '">' . SP()->text->string('View all events') . '</a>';
	?>
</div>