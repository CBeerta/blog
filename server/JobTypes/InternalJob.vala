/**

TODO


 - i guess this would be a good place to plug the http server?
 - would we do error monitoring here? or rather in the mainloop to have access to everything?
 


**/


public class InternalJob : Job
{
    private bool has_run { get; private set; default = false; }
    
    public InternalJob()
    {
        jobtype = JobTypes.INTERNALJOB;
    }
    
    public override bool run ()
    {

        return true;
    }
}
