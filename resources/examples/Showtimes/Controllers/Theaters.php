<?php
namespace Mill\Examples\Showtimes\Controllers;

/**
 * @api-label Movie Theaters
 */
class Theaters
{
    /**
     * Returns all movie theatres for a specific location.
     *
     * @api-label Get movie theaters.
     * @api-group Theaters
     *
     * @api-path:public /theaters
     *
     * @api-param:public location (string, required) - Location you want theaters in.
     *
     * @api-return:public {collection} \Mill\Examples\Showtimes\Representations\Theater
     *
     * @api-error:public 400 (\Mill\Examples\Showtimes\Representations\Error) - If the location is invalid.
     *
     * @api-version >=1.1.2
     * @api-contentType application/mill.example.theater
     *
     * @api-version <1.1.2
     * @api-contentType application/json
     */
    public function GET()
    {
        //
    }

    /**
     * Create a new movie theater.
     *
     * @api-label Create a movie theater.
     * @api-group Theaters
     *
     * @api-path:public /theaters
     *
     * @api-scope create
     *
     * @api-param:public name (string, required) - Name of the theater.
     * @api-param:public address (string, required) - Theater address
     * @api-param:public phone_number (string, required) - Theater phone number
     *
     * @api-return:public {object} \Mill\Examples\Showtimes\Representations\Theater
     *
     * @api-error:public 400 (\Mill\Examples\Showtimes\Representations\Error) - If there is a problem with the request.
     *
     * @api-version >=1.1.2
     * @api-contentType application/mill.example.theater
     *
     * @api-version <1.1.2
     * @api-contentType application/json
     */
    public function POST()
    {
        //
    }
}
