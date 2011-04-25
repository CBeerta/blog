public class CronJob : Job
{
    public DateTime last_run { get; set; }

    public CronJob ()
    {
        jobtype = JobTypes.CRONJOB;
    }

    public CronJob.from_name(string n)
    {
        last_run = new DateTime.now(tz);
        last_run = last_run.add_minutes(-1); //FIXME REMOVEME this is for debugging

        name = n;
        
        run_at = "0-10,10-40,40-60 * * * *";

        load();
    }
    
    private bool in_range (string range, int check)
    {
        foreach (var r in range.split(","))
        {
            var min_max = r.split("-");
            
            if ( min_max.length != 2)
                continue;
            
            if ( check >= int.parse(min_max[0]) && check <= int.parse(min_max[1]) )
                return true;
        }
        return false;
    }

    public override bool run ()
    {
        var now = new DateTime.now(tz);
        var run_at = this.run_at.split(" ");

        if ( now.get_minute() == last_run.get_minute() && now.get_hour() == last_run.get_hour())
            return false;
        else
            last_run = new DateTime.now(tz);
            
        // Disabled, or not ready to run again
        if ( this.run_at == "-" )
            return false;
        
        if ( run_at.length != 5 )
        {
            warning ("%s: run_at has an invalid run_at: %s", this.name, this.run_at);
            return false;
        }
        
        // check minutes
        if ( ! (run_at[0] == "*" || now.get_minute().to_string() == run_at[0] || in_range (run_at[0], now.get_minute())) )
            return false;

        // check hour
        if ( ! (run_at[1] != "*" || now.get_hour().to_string() != run_at[1] || in_range (run_at[1], now.get_hour())) )
            return false;

        // check day of month
        if ( ! (run_at[2] == "*" || now.get_day_of_month().to_string() == run_at[2]) || in_range (run_at[2], now.get_day_of_month()) )
            return false;

        // check month
        if ( ! (run_at[3] == "*" || now.get_month().to_string() == run_at[3]) || in_range (run_at[3], now.get_month()) )
            return false;

        // check day of week
        if ( ! (run_at[4] == "*" || now.get_day_of_week().to_string() == run_at[4]) || in_range (run_at[4], now.get_day_of_week()) )
            return false;

        debug ("CronJob.run(): %s", name);    
                
        return true;
    }
}

