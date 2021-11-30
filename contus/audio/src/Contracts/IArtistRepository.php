<?php

/**
 * Implements of IArtistRepository
 *
 * Inteface for implementing the ArtistRepository modules and functions  
 * 
 * @name       IArtistRepository
 * @version    1.0
 * @author     Contus<developers@contus.in>
 * @copyright  Copyright (C) 2016 Contus. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Contracts;

interface IArtistRepository
{

  /**
   * Store a newly created Artists.
   *
   * @param array $id
   *          input values
   *          
   * @return void
   */
  public function addOrUpdateArtist($id = null);

  /**
   * Fetch users to display in Artists block.
   *
   * @param array $status
   *          input values
   *          
   * @return response
   */
  public function getArtists($status);

  /**
   * Fetch user to edit.
   *
   * @return response
   */
  public function getArtist($id);
}
