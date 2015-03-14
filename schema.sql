CREATE TABLE dj_actions (
	id		SERIAL,
	dj_command	TEXT,
	dj_arg		TEXT,
	slack_user	TEXT,
	retrieved	BOOLEAN
);

