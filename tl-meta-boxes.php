<?php

/**
 * Meta boxes class
 *
 * @package The_Loops
 * @since 0.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'TL_Meta_Boxes' ) ) :
class TL_Meta_Boxes {

	/**
	 * Meta boxes loaded
	 *
	 * @package The_Loops
	 * @since 0.3
	 */
	public static function init() {
		add_meta_box( 'tl_generaldiv', __( 'General' ), array( __CLASS__, 'meta_box_general' ), 'tl_loop', 'normal' );
		add_meta_box( 'tl_taxonomydiv', __( 'Taxonomy Parameters' ), array( __CLASS__, 'meta_box_taxonomy' ), 'tl_loop', 'normal' );
		add_meta_box( 'tl_customfielddiv', __( 'Custom Field Parameters' ), array( __CLASS__, 'meta_box_custom_field' ), 'tl_loop', 'normal' );
		add_meta_box( 'tl_displaydiv', __( 'Display' ), array( __CLASS__, 'meta_box_display' ), 'tl_loop', 'normal' );
	}

	/**
	 * Display metabox for setting the content of the loop
	 *
	 * @package The_Loops
	 * @since 0.3
	 */
	public static function meta_box_general() {
		global $post_ID;

		wp_nonce_field( 'tl_edit_loop', '_tlnonce' );

		$content = get_post_meta( $post_ID, 'tl_loop_content', true );

		$defaults = array(
			'post_type' => array( 'post' ), 'orderby' => 'title', 'order' => 'ASC',
			'not_found' => '<p>' . __( 'Nothing found!' ) . '</p>',
			'authors' => '',
			'date' => array(
				'min' => '',
				'max' => '',
			)
		);
		$content = wp_parse_args( $content, $defaults );
?>
<table class="form-table">
	<tr valign="top">
	<th scope="row"><label for="loop_post_type"><?php _e( 'Display' ); ?></label></th>
		<td>
			<select id="loop_post_type" name="loop[post_type][]" multiple="multiple">
				<?php
				$ptypes = get_post_types( array( 'public' => true ), 'objects' );
				foreach ( $ptypes as $ptype_name => $ptype_obj ) {
					$selected = in_array( $ptype_name, $content['post_type'] ) ? ' selected="selected"' : '';
					echo "<option value='" . esc_attr( $ptype_name ) . "'$selected>{$ptype_obj->label}</option>";
				}
				?>
			</select>
		</td>
	</tr>
	<tr valign="top">
	<th scope="row"><label for="loop_orderby"><?php _e( 'Sorted by' ); ?></label></th>
		<td>
			<select id="loop_orderby" name="loop[orderby]">
				<?php
				$orderby_params = array(
					'ID' => __( 'ID' ), 'author' => __( 'Author' ), 'title' => __( 'Title' ),
					'date' => __( 'Publication date' ), 'modified' => __( 'Last modified date' ), 'parent' => __( 'Parent ID' ),
					'rand' => __( 'Random order' ), 'comment_count' => __( 'Number of comments' ), 'menu_order' => __( 'Page order' )
				);
				foreach ( $orderby_params as $key => $label ) {
					$selected = selected( $key, $content['orderby'] );
					echo "<option value='" . esc_attr( $key ) . "'$selected>{$label}</option>";
				}
				?>
			</select>
			<select id="loop_order" name="loop[order]">
				<option value="DESC"<?php selected( 'DESC', $content['order'], true ); ?>><?php _e( 'DESC' ); ?></option>
				<option value="ASC"<?php selected( 'ASC', $content['order'], true ); ?>><?php _e( 'ASC' ); ?></option>
			</select>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop-min-date"><?php _e( 'Date range' ); ?></label></th>
		<td>
			from
			<input type="text" class="loop-date" id="loop-min-date" name="loop[date][min]" value="<?php echo esc_attr( $content['date']['min'] ); ?>" class="regular-text" />
			to
			<input type="text" class="loop-date" id="loop-max-date" name="loop[date][max]" value="<?php echo esc_attr( $content['date']['max'] ); ?>" class="regular-text" />
			<span class="description"><?php _e( 'If these fields are left empty, infinite values will be used' ); ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop_authors"><?php _e( 'Authors' ); ?></label></th>
		<td>
			<input type="text" id="loop_authors" name="loop[authors]" value="<?php echo esc_attr( $content['authors'] ); ?>" class="regular-text" />
			<span class="description"><?php _e( "Comma-separated list of authors usernames. Exclude an author by prefixing the username with a '-' (minus) sign." ); ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop_not_found"><?php _e( 'Not found text' ); ?></label></th>
		<td>
			<input type="text" id="loop_not_found" name="loop[not_found]" value="<?php echo esc_attr( $content['not_found'] ); ?>" class="regular-text" />
			<span class="description"><?php _e( 'Text to display when nothing found' ); ?></span>
		</td>
	</tr>
</table>
<?php
	}

	/**
	 * Display metabox for setting the loop taxonomy parameters
	 *
	 * @package The_Loops
	 * @since 0.3
	 */
	public static function meta_box_taxonomy() {
		global $post_ID;

		$content = get_post_meta( $post_ID, 'tl_loop_content', true );

		$defaults = array(
			'taxonomies' => array()
		);
		$content = wp_parse_args( $content, $defaults );
		extract( $content );

		$taxs = get_taxonomies( array( 'public' => true ), 'objects' );
?>
<?php foreach ( $taxonomies as $key => $taxonomy ) : ?>
	<table class="form-table tl-parameter">
		<tr valign="top">
			<th scope="row">
				<label for="loop_taxonomies_<?php echo $key; ?>_taxonomy"><?php _e( 'Taxonomy' ); ?></label>
			</th>
			<td>
				<select id="loop_taxonomies_<?php echo $key; ?>_taxonomy" name="loop[taxonomies][<?php echo $key; ?>][taxonomy]">
					<?php
					foreach ( $taxs as $tax ) {
						$selected = selected( $taxonomy['taxonomy'], $tax->name );
						echo "<option value='{$tax->name}'$selected>{$tax->labels->name}</option>";
					}
					?>
				</select>
				<a href="#" class="tl-delete"><?php _e( 'remove' ); ?></a>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="loop_taxonomies_<?php echo $key; ?>_terms"><?php _e( 'Terms' ); ?></label></th>
			<td>
				<input value="<?php echo esc_attr( $taxonomy['terms'] ); ?>" type="text" id="loop_taxonomies_<?php echo $key; ?>_terms" name="loop[taxonomies][<?php echo $key; ?>][terms]" class="regular-text" />
				<span class="description"><?php _e( 'Comma-separated list of slugs' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="loop_taxonomies_<?php echo $key; ?>_exclude"><?php _e( 'Exclude' ); ?></label></th>
			<td>
				<?php $exclude = isset( $taxonomy['exclude'] ) ? $taxonomy['exclude'] : '0'; ?>
				<?php $checked = checked( $exclude, '1', false ); ?>
				<input<?php echo $checked; ?> type="checkbox" id="loop_taxonomies_<?php echo $key; ?>_exclude" name="loop[taxonomies][<?php echo $key; ?>][exclude]" value="1" />
				<span class="description"><?php _e( 'Hide the terms above instead of showing them' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="loop_taxonomies_<?php echo $key; ?>_include_children"><?php _e( 'Include children' ); ?></label></th>
			<td>
				<?php $include_children = isset( $taxonomy['include_children'] ) ? $taxonomy['include_children'] : '0'; ?>
				<?php $checked = checked( $include_children, '1', false ); ?>
				<input<?php echo $checked; ?> type="checkbox" id="loop_taxonomies_<?php echo $key; ?>_include_children" name="loop[taxonomies][<?php echo $key; ?>][include_children]" value="1" />
				<span class="description"><?php _e( 'Include children terms (for hierarchical taxonomies)' ); ?></span>
			</td>
		</tr>
	</table>
<?php endforeach; ?>

<p><a class="tl-add-parameter button" href="#"><?php _e( 'New Parameter' ); ?></a></p>

<table class="form-table tl-parameter hide-if-js">
		<tr valign="top">
			<th scope="row">
				<label for="loop_taxonomies_{key}_taxonomy"><?php _e( 'Taxonomy' ); ?></label>
			</th>
			<td>
				<select id="loop_taxonomies_{key}_taxonomy" name="loop[taxonomies][{key}][taxonomy]">
					<?php
					foreach ( $taxs as $tax ) {
						echo "<option value='{$tax->name}'>{$tax->labels->name}</option>";
					}
					?>
				</select>
				<a href="#" class="tl-delete"><?php _e( 'remove' ); ?></a>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="loop_taxonomies_{key}_terms"><?php _e( 'Terms' ); ?></label></th>
			<td>
				<input value="" type="text" id="loop_taxonomies_{key}_terms" name="loop[taxonomies][{key}][terms]" class="regular-text" />
				<span class="description"><?php _e( 'Comma-separated list of slugs' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="loop_taxonomies_{key}_exclude"><?php _e( 'Exclude' ); ?></label></th>
			<td>
				<input type="checkbox" id="loop_taxonomies_{key}_exclude" name="loop[taxonomies][{key}][exclude]" value="1" />
				<span class="description"><?php _e( 'Check if you want to hide the terms above instead of showing them' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="loop_taxonomies_{key}_include_children"><?php _e( 'Include children' ); ?></label></th>
			<td>
				<input type="checkbox" id="loop_taxonomies_{key}_include_children" name="loop[taxonomies][{key}][include_children]" checked="checked" value="1" />
				<span class="description"><?php _e( 'Include children terms (for hierarchical taxonomies)' ); ?></span>
			</td>
		</tr>
</table>
<?php
	}

	/**
	 * Display metabox for setting the loop custom field parameters
	 *
	 * @package The_Loops
	 * @since 0.3
	 */
	public static function meta_box_custom_field() {
		global $post_ID;

		$content = get_post_meta( $post_ID, 'tl_loop_content', true );

		$defaults = array(
			'custom_fields' => array()
		);
		$content = wp_parse_args( $content, $defaults );
		extract( $content );
?>
<?php foreach ( $custom_fields as $key => $custom_field ) : ?>
	<table class="form-table tl-parameter">
		<tr valign="top">
			<th scope="row">
				<label for="loop_custom_fields_<?php echo $key; ?>_key"><?php _e( 'Key' ); ?></label>
			</th>
			<td>
				<input value="<?php echo esc_attr( $custom_field['key'] ); ?>" type="text" id="loop_custom_fields_<?php echo $key; ?>_key" name="loop[custom_fields][<?php echo $key; ?>][key]" class="regular-text" />
				<a href="#" class="tl-delete"><?php _e( 'remove' ); ?></a>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="loop_custom_fields_<?php echo $key; ?>_compare"><?php _e( 'Comparison' ); ?></label></th>
			<td>
				<select id="loop_custom_fields_<?php echo $key; ?>_compare" name="loop[custom_fields][<?php echo $key; ?>][compare]">
					<option<?php selected( $custom_field['compare'], 'IN' ); ?> value="IN"><?php _e( 'is equal to' ); ?></option>
					<option<?php selected( $custom_field['compare'], 'NOT IN' ); ?> value="NOT IN"><?php _e( 'is not equal to' ); ?></option>
					<option<?php selected( $custom_field['compare'], 'LIKE' ); ?> value="LIKE"><?php _e( 'contains' ); ?></option>
					<option<?php selected( $custom_field['compare'], 'NOT LIKE' ); ?> value="NOT LIKE"><?php _e( "doesn't contain" ); ?></option>
					<option<?php selected( $custom_field['compare'], 'BETWEEN' ); ?> value="BETWEEN"><?php _e( 'is between' ); ?></option>
					<option<?php selected( $custom_field['compare'], 'NOT BETWEEN' ); ?> value="NOT BETWEEN"><?php _e( 'is not between' ); ?></option>
				</select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="loop_custom_fields_<?php echo $key; ?>_values"><?php _e( 'Values' ); ?></label></th>
			<td>
				<input value="<?php echo esc_attr( $custom_field['values'] ); ?>" type="text" id="loop_custom_fields_<?php echo $key; ?>_values" name="loop[custom_fields][<?php echo $key; ?>][values]" class="regular-text tl-tagsinput" />
				<span class="description"><?php _e( 'Press TAB or ENTER to add several values' ); ?></span><br />
				<span class="description"><?php _e( 'Add only one value for "contains" and "doesn\'t contain" comparisons' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="loop_custom_fields_<?php echo $key; ?>_type"><?php _e( 'Type' ); ?></label></th>
			<td>
				<select id="loop_custom_fields_<?php echo $key; ?>_type" name="loop[custom_fields][<?php echo $key; ?>][type]">
					<?php $type = $custom_field['type']; ?>
					<option<?php selected( $custom_field['type'], 'CHAR' ); ?>>CHAR</option>
					<option<?php selected( $custom_field['type'], 'NUMERIC' ); ?>>NUMERIC</option>
					<option<?php selected( $custom_field['type'], 'DECIMAL' ); ?>>DECIMAL</option>
					<option<?php selected( $custom_field['type'], 'SIGNED' ); ?>>SIGNED</option>
					<option<?php selected( $custom_field['type'], 'UNSIGNED' ); ?>>UNSIGNED</option>
					<option<?php selected( $custom_field['type'], 'DATE' ); ?>>DATE</option>
					<option<?php selected( $custom_field['type'], 'DATETIME' ); ?>>DATETIME</option>
					<option<?php selected( $custom_field['type'], 'TIME' ); ?>>TIME</option>
					<option<?php selected( $custom_field['type'], 'BINARY' ); ?>>BINARY</option>
				</select>
				<span class="description"><?php _e( "Leave the default if you don't know what this means" ); ?></span>
			</td>
		</tr>
	</table>
<?php endforeach; ?>

<p><a class="tl-add-parameter button" href="#"><?php _e( 'New Parameter' ); ?></a></p>

<table class="form-table tl-parameter hide-if-js">
	<tr valign="top">
		<th scope="row">
			<label for="loop_custom_fields_{key}_key"><?php _e( 'Key' ); ?></label>
		</th>
		<td>
			<input value="" type="text" id="loop_custom_fields_{key}_key" name="loop[custom_fields][{key}][key]" class="regular-text" />
			<a href="#" class="tl-delete"><?php _e( 'remove' ); ?></a>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop_custom_fields_{key}_compare"><?php _e( 'Comparison' ); ?></label></th>
		<td>
			<select id="loop_custom_fields_{key}_compare" name="loop[custom_fields][{key}][compare]">
				<option value="IN"><?php _e( 'is equal to' ); ?></option>
				<option value="NOT IN"><?php _e( 'is not equal to' ); ?></option>
				<option value="LIKE"><?php _e( 'contains' ); ?></option>
				<option value="NOT LIKE"><?php _e( "doesn't contain" ); ?></option>
				<option value="BETWEEN"><?php _e( 'is between' ); ?></option>
				<option value="NOT BETWEEN"><?php _e( 'is not between' ); ?></option>
			</select>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop_custom_fields_{key}_values"><?php _e( 'Values' ); ?></label></th>
		<td>
			<input value="" type="text" id="loop_custom_fields_{key}_values" name="loop[custom_fields][{key}][values]" class="regular-text tl-tagsinput" />
			<span class="description"><?php _e( 'Press TAB or ENTER to add several values' ); ?></span><br />
			<span class="description"><?php _e( 'Add only one value for "contains" and "doesn\'t contain" comparisons' ); ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop_custom_fields_{key}_type"><?php _e( 'Type' ); ?></label></th>
		<td>
			<select id="loop_custom_fields_{key}_type" name="loop[custom_fields][{key}][type]">
				<option>CHAR</option>
				<option>NUMERIC</option>
				<option>DECIMAL</option>
				<option>SIGNED</option>
				<option>UNSIGNED</option>
				<option>DATE</option>
				<option>DATETIME</option>
				<option>TIME</option>
				<option>BINARY</option>
			</select>
			<span class="description"><?php _e( "Leave the default if you don't know what this means" ); ?></span>
		</td>
	</tr>
</table>
<?php
	}

	/**
	 * Display metabox for the loop display settings
	 *
	 * @package The_Loops
	 * @since 0.3
	 */
	public static function meta_box_display() {
		global $post_ID;

		$content = get_post_meta( $post_ID, 'tl_loop_content', true );

		$defaults = array(
			'pagination'     => '',
			'posts_per_page' => get_option( 'posts_per_page' ),
			'template'       => 'List of full posts'
		);
		$content = wp_parse_args( $content, $defaults );

		$loop_templates = tl_get_loop_templates();
?>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><label for="loop_posts_per_page"><?php _e( 'Show' ); ?></label></th>
		<td>
			<input type="text" id="loop_posts_per_page" name="loop[posts_per_page]" value="<?php echo esc_attr( $content['posts_per_page'] ); ?>" size="3" />
			<span><?php _e( 'items on the page' ); ?></span>
			<span class="description"><?php _e( 'If this is left empty, all the items will be displayed' ); ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop_pagination"><?php _e( 'Pagination' ); ?></label></th>
		<td>
			<select id="loop_pagination" name="loop[pagination]">
				<option value=""><?php _e( 'none' ); ?></option>
				<option<?php selected( $content['pagination'], 'previous_next' ); ?> value="previous_next"><?php _e( 'previous and next links only' ); ?></option>
				<option<?php selected( $content['pagination'], 'numeric' ); ?> value="numeric"><?php _e( 'numeric' ); ?></option>
			</select>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop_template"><?php _e( 'Template' ); ?></label></th>
		<td>
			<select id="loop_template" name="loop[template]">
				<?php
				foreach ( $loop_templates as $name => $file ) {
					$selected = selected( $name, $content['template'] );
					echo "<option value='" . esc_attr( $name ) . "'$selected>{$name}</option>";
				}
				?>
			</select>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'Shortcode' ); ?></th>
		<td>
			<code><?php echo '[the-loop id="' . $post_ID . '"]'; ?></code>
			<span class="description"><?php _e( 'To use the shortcode, copy/paste it in the post or page where you want to display the loop' ); ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'Widget' ); ?></th>
		<td>
			<span class="description"><?php printf( __( 'To use the widget, <a href="%s">go to the widgets management screen</a> and assign The Loops widget to a sidebar' ), site_url( 'wp-admin/widgets.php' ) ) ?></span>
		</td>
	</tr>
</table>
<?php
	}

}
endif;
