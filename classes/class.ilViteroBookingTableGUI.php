<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Vitero Booking table GUI
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup 
 */
class ilViteroBookingTableGUI extends ilTable2GUI
{

	private $editable = false;
	private $admin_table = false;


	/**
	 * Init table
	 */
	public function init()
	{
		$this->setFormAction($GLOBALS['ilCtrl']->getFormAction($this->getParentObject()));

		if(!$this->isEditable())
		{
			$this->setRowTemplate('tpl.booking_list_row.html', substr(ilViteroPlugin::getInstance()->getDirectory(),2));
			$this->setTitle(ilViteroPlugin::getInstance()->txt('app_table'));
			$this->addColumn(ilViteroPlugin::getInstance()->txt('app_tbl_col_time'),'startt','55%');
			$this->addColumn(ilViteroPlugin::getInstance()->txt('app_tbl_col_dur'),'duration','15%');
			$this->addColumn(ilViteroPlugin::getInstance()->txt('app_tbl_col_rec'),'rec','15%');
			$this->addColumn(ilViteroPlugin::getInstance()->txt('app_tbl_col_ends'),'ends','15%');
		}
		else
		{
			$this->setRowTemplate('tpl.booking_list_row.html', substr(ilViteroPlugin::getInstance()->getDirectory(),2));
			$this->setTitle(ilViteroPlugin::getInstance()->txt('app_table'));
			$this->addColumn(ilViteroPlugin::getInstance()->txt('app_tbl_col_time'),'startt','45%');
			$this->addColumn(ilViteroPlugin::getInstance()->txt('app_tbl_col_dur'),'duration','15%');
			$this->addColumn(ilViteroPlugin::getInstance()->txt('app_tbl_col_rec'),'rec','15%');
			$this->addColumn(ilViteroPlugin::getInstance()->txt('app_tbl_col_ends'),'ends','15%');
			$this->addColumn($GLOBALS['lng']->txt('actions'),'','10%');
		}

		$this->setDefaultOrderField('startt');
	}

	public function setAdminTable($a_stat)
	{
		$this->admin_table = $a_stat;
	}

	public function isAdminTable()
	{
		return $this->admin_table;
	}


	/**
	 * Set Editable
	 * @param <type> $a_status
	 */
	public function setEditable($a_status)
	{
		$this->editable = $a_status;
	}

	public function isEditable()
	{
		return (bool) $this->editable;
	}

		/**
	 * Fill template row
	 * @param <type> $a_set
	 */
	public function  fillRow($a_set)
	{
		$this->tpl->setVariable('TIME', $a_set['time']);
		$this->tpl->setVariable('DURATION', $a_set['duration']);
		$this->tpl->setVariable('REC',  ilViteroUtils::recurrenceToString($a_set['rec']));

		if($this->isAdminTable())
		{
			include_once './Services/Tree/classes/class.ilPathGUI.php';
			$path = new ilPathGUI();
			$path->setUseImages(false);
			$path->enableTextOnly(false);
			$this->tpl->setVariable('OBJ_PATH',$path->getPath(ROOT_FOLDER_ID, end(ilObject::_getAllReferences($a_set['group']))));
		}



		if($a_set['rec'])
		{
			$this->tpl->setVariable('ENDS', $a_set['ends']);
		}
		else
		{
			$this->tpl->setVariable('ENDS','');
		}

		if(!$this->isEditable())
		{
			return true;
		}


		include_once './Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
		$list = new ilAdvancedSelectionListGUI();
		$list->setId('booka_'.$a_set['start']->get(IL_CAL_UNIX).'_'.$a_set['id']);
		$list->setListTitle($this->lng->txt('actions'));


		// no recurrence
		if($a_set['rec'] == 0)
		{
			$GLOBALS['ilCtrl']->setParameter(
				$this->getParentObject(),
				'bookid',
				$a_set['id']
			);

			$list->addItem(
				ilViteroPlugin::getInstance()->txt('edit_booking'),
				'',
				$GLOBALS['ilCtrl']->getLinkTarget($this->getParentObject(),'editBooking')
			);

			// delete appointment
			$list->addItem(
				ilViteroPlugin::getInstance()->txt('delete_appointment'),
				'',
				$GLOBALS['ilCtrl']->getLinkTarget($this->getParentObject(),'confirmDeleteAppointment')
			);

		}
		// A recurrence
		if($a_set['rec'] > 0)
		{

			// Delete single appointment
			$GLOBALS['ilCtrl']->setParameter(
				$this->getParentObject(),
				'atime',
				$a_set['start']->get(IL_CAL_UNIX)
			);
			$GLOBALS['ilCtrl']->setParameter(
				$this->getParentObject(),
				'bookid',
				$a_set['id']
			);

			$list->addItem(
				ilViteroPlugin::getInstance()->txt('edit_bookings'),
				'',
				$GLOBALS['ilCtrl']->getLinkTarget($this->getParentObject(),'editBooking')
			);


			// not supported
			/*
			$list->addItem(
				ilViteroPlugin::getInstance()->txt('delete_appointment'),
				'',
				$GLOBALS['ilCtrl']->getLinkTarget($this->getParentObject(),'confirmDeleteAppointmentInSeries')
			);
			*/
			// Delete appointment series
			$list->addItem(
				ilViteroPlugin::getInstance()->txt('delete_reccurrence'),
				'',
				$GLOBALS['ilCtrl']->getLinkTarget($this->getParentObject(),'confirmDeleteBooking')
			);
		}
		$this->tpl->setVariable('ACTION_PART',$list->getHTML());


	}

	public function parseAdminTable(ilDateTime $start, ilDateTime $end)
	{
		$booking_list = array();

		try {
			$con = new ilViteroBookingSoapConnector();
			$bookings = $con->getBookingListByDate(ilViteroSettings::getInstance()->getCustomer(),$start,$end);
		}
		catch(Exception $e) {
			throw $e;
		}

		$booking_arr = array();
		if(is_object($bookings->booking))
		{
			$booking_arr = array($bookings->booking);
		}
		elseif(is_array($bookings->booking))
		{
			$booking_arr = $bookings->booking;
		}

		$counter = 0;
		foreach($booking_arr as $booking)
		{
			$fstart = ilViteroUtils::parseSoapDate($booking->start);
			$fend = ilViteroUtils::parseSoapDate($booking->end);
			$duration = $fend->get(IL_CAL_UNIX) - $fstart->get(IL_CAL_UNIX);
			

			$booking_list[$counter]['rec'] = $booking->repetitionpattern;
			$booking_list[$counter]['id'] = $booking->bookingid;
			$booking_list[$counter]['start'] = $fstart;
			$booking_list[$counter]['startt'] = $fstart->get(IL_CAL_UNIX);

			$bend = ilViteroUtils::parseSoapDate($booking->end);
			$booking_list[$counter]['end'] = $bend;

			if($booking->cafe)
			{
				$booking_list[$counter]['start'] = new ilDate($booking_list[$counter]['startt'], IL_CAL_UNIX);
				$booking_list[$counter]['time'] = ilDatePresentation::formatDate(
						$booking_list[$counter]['start']
				);
			}
			else
			{
				$booking_list[$counter]['time'] = ilDatePresentation::formatPeriod(
						$booking_list[$counter]['start'],
						$booking_list[$counter]['end']
				);
			}

			$booking_list[$counter]['duration'] = ilFormat::_secondsToString(
					$booking_list[$counter]['end']->get(IL_CAL_UNIX) - $booking_list[$counter]['start']->get(IL_CAL_UNIX),
					false
			);
			if($booking->repetitionpattern)
			{
				$repend = ilViteroUtils::parseSoapDate($booking->repetitionenddate);
				$booking_list[$counter]['ends'] = ilDatePresentation::formatDate(new ilDate($repend->get(IL_CAL_UNIX), IL_CAL_UNIX));
			}

			$booking_list[$counter]['group'] = ilObjVitero::lookupObjIdByGroupId($booking->groupid);

			$counter++;
		}

		$this->setMaxCount(count($booking_list));
		$this->setData($booking_list);
	}


	/**
	 * Parse bookings
	 * @param int $a_groupid
	 * @param ilDate $start
	 * @param ilDate $end
	 *
	 * throws ilViteroConnectionException
	 */
	public function parse($a_groupid, ilDateTime $start, ilDateTime $end)
	{
		$booking_list = array();


		try {
			$con = new ilViteroBookingSoapConnector();
			$bookings = $con->getByGroupAndDate($a_groupid, $start, $end);
		}
		catch(Exception $e) {
			throw $e;
		}

		$booking_arr = array();
		if(is_object($bookings->booking))
		{
			$booking_arr = array($bookings->booking);
		}
		elseif(is_array($bookings->booking))
		{
			$booking_arr = $bookings->booking;
		}


		$counter = 0;
		foreach($booking_arr as $booking)
		{
			$fstart = ilViteroUtils::parseSoapDate($booking->start);
			$fend = ilViteroUtils::parseSoapDate($booking->end);
			$duration = $fend->get(IL_CAL_UNIX) - $fstart->get(IL_CAL_UNIX);

			foreach(ilViteroUtils::calculateBookingAppointments($start, $end, $booking) as $dl)
			{
				$booking_list[$counter]['rec'] = $booking->repetitionpattern;
				$booking_list[$counter]['id'] = $booking->bookingid;
				$booking_list[$counter]['start'] = $dl;
				$booking_list[$counter]['startt'] = $dl->get(IL_CAL_UNIX);

				$bend = clone $dl;
				$bend->setDate($dl->get(IL_CAL_UNIX) + $duration,IL_CAL_UNIX);

				$booking_list[$counter]['end'] = $bend;

				if($booking->cafe)
				{
					$booking_list[$counter]['start'] = new ilDate($booking_list[$counter]['startt'],IL_CAL_UNIX);
					$booking_list[$counter]['time'] = ilDatePresentation::formatDate(
						$booking_list[$counter]['start']
					);
				}
				else
				{
					$booking_list[$counter]['time'] = ilDatePresentation::formatPeriod(
						$booking_list[$counter]['start'],
						$booking_list[$counter]['end']
					);
				}
				
				$booking_list[$counter]['duration'] = ilFormat::_secondsToString(
					$booking_list[$counter]['end']->get(IL_CAL_UNIX) - $booking_list[$counter]['start']->get(IL_CAL_UNIX),
					false
				);
				if($booking->repetitionpattern)
				{
					$repend = ilViteroUtils::parseSoapDate($booking->repetitionenddate);
					$booking_list[$counter]['ends'] = ilDatePresentation::formatDate(new ilDate($repend->get(IL_CAL_UNIX),IL_CAL_UNIX));
				}
				$counter++;
			}
		}
	
		$this->setMaxCount(count($booking_list));
		$this->setData($booking_list);
	}
}
?>