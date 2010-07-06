<?php



function get_commit_class($commit_ref)
{
    return preg_match('/^head/',$commit_ref) ? 'head' : (
	    preg_match('/^remote/',$commit_ref) ? 'remote' : ''
	);
}
