CREATE TABLE rate (
	currency_from     VARCHAR(3) NOT NULL,
	currency_to       VARCHAR(3) NOT NULL,
	rate              FLOAT      NOT NULL,
	date_time_checked DATETIME   NOT NULL,

	PRIMARY KEY (currency_from, currency_to)
);
