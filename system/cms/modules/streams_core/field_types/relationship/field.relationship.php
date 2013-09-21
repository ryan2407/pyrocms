<?php defined('BASEPATH') or exit('No direct script access allowed');

use Pyro\Module\Streams_core\Core\Field\AbstractField;
use Pyro\Module\Streams_core\Core\Model;

/**
 * PyroStreams Relationship Field Type
 *
 * @package		PyroCMS\Core\Modules\Streams Core\Field Types
 * @author		Parse19
 * @copyright	Copyright (c) 2011 - 2012, Parse19
 * @license		http://parse19.com/pyrostreams/docs/license
 * @link		http://parse19.com/pyrostreams
 */
class Field_relationship extends AbstractField
{
	public $field_type_slug			= 'relationship';

	public $db_col_type				= 'integer';

	public $custom_parameters		= array( 'choose_stream', 'link_uri');

	public $version					= '1.1.0';

	public $author					= array('name'=>'Parse19', 'url'=>'http://parse19.com');

	/**
	 * Run time cache
	 */
	private $cache;

	public function relation()
	{
		return $this->belongsToEntry($this->getParameter('relation_class', 'Pyro\Module\Streams_core\Core\Model\Entry'));
	}

	/**
	 * Output form input
	 *
	 * @param	array
	 * @param	array
	 * @return	string
	 */
	public function form_output()
	{
		$model = Model\Entry::stream($this->getParameter('choose_stream'));

		$stream = $model->getStream();

		$title_column = $title_column = $model->getTitleColumn();

		$entry_options = array();

		// If this is not required, then
		// let's allow a null option
		if ( ! $this->field->is_required)
		{
			$entry_options[null] = ci()->config->item('dropdown_choose_null');
		}

		// Get the entries
		$entry_options += $model->lists($title_column, $model->getKeyName());
		

		

		// Output the form input
		return form_dropdown($this->form_slug, $entry_options, $this->value, 'id="'.rand_string(10).'"');
	}

	// --------------------------------------------------------------------------

	/**
	 * Get a list of streams to choose from
	 *
	 * @return	string
	 */
	public function param_choose_stream($stream_id = false)
	{
		$options = Model\Stream::getStreamAssociativeOptions();

		return form_dropdown('choose_stream', $options, $stream_id);
	}

	// --------------------------------------------------------------------------

	/**
	 * Pre Ouput
	 *
	 * Process before outputting on the CP. Since
	 * there is less need for performance on the back end,
	 * this is accomplished via just grabbing the title column
	 * and the id and displaying a link (ie, no joins here).
	 *
	 * @param	array 	$input
	 * @return	mixed 	null or string
	 */
	public function pre_output()
	{
		if($entry = $this->getRelation())
		{
			$stream = $entry->getStream();

			$title_column = $entry->getTitleColumn();
		
			if (ci()->uri->segment(1) == 'admin')
			{
				if ($url = $this->getParameter('link_uri'))
				{
					$entry_array = $entry->toArray();

					$entry_array['entry_stream_slug'] = $stream->stream_slug;

					// Support Lex tags
					$url = ci()->parser->parse_string($url, $entry_array, true);

					// This is kept for backwards compatibility
					$url = str_replace(array('-id-', '-stream-'), array($entry->getKey(), $stream->stream_slug), $url);

					return '<a href="'.site_url($url).'">'.$entry->$title_column.'</a>';
				}
				else
				{
					return '<a href="'.site_url('admin/streams/entries/view/'.$stream->id.'/'.$entry->getKey()).'">'.$entry->$title_column.'</a>';
				}
			}
			else
			{
				return $entry->toArray();
			}			
		}

		return null;
	}

	/**
	 * Pre Ouput Plugin
	 *
	 * This takes the data from the join array
	 * and formats it using the row parser.
	 *
	 * @param	array 	$row 		the row data from the join
	 * @param	array  	$custom 	custom field data
	 * @param	mixed 	null or formatted array
	 */
	public function pre_output_plugin()
	{
		if ($entry = $this->getRelation())
		{
			return $entry->toArray();
		}

		return null;
	}

}
