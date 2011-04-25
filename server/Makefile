EXEC=server
PACKAGES=--pkg gee-1.0 --pkg gio-2.0 --pkg posix
SOURCES=Cron.vala Config.vala Job.vala JobTypes/CronJob.vala JobTypes/RunnerJob.vala JobTypes/TriggerJob.vala JobTypes/InternalJob.vala Main.vala
FLAGS=-DDEBUG

all:
	valac --thread -v $(PACKAGES) -X $(FLAGS) $(SOURCES) -o $(EXEC)

