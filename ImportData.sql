DROP PROCEDURE IF exists ImportData;

DELIMITER ;;
CREATE PROCEDURE ImportData(IN datum_start DATE, IN datum_end DATE)
BEGIN
	DECLARE i INT DEFAULT 0; 
	DECLARE n INT DEFAULT 0;
	DECLARE x CHAR(2);
    DECLARE y INT DEFAULT 0;

    SET i=1;
    SET n=24;
    
/*WHILE DATEDIFF(datum_start,datum_end) <> 1 DO */

WHILE datum_end >= datum_start DO
    WHILE i<=n DO 
		
        SET  y=i-1;
 
        IF i<10 THEN 
			SET x = CONCAT('0',i);
			SET y = CONCAT('0',y);
        ELSE
			SET x=i;
            
		END IF;
       
        
   
		SET @sql = CONCAT('
     INSERT INTO siemens_view(date_timestamp,date,hours,Accounted_losses,34Z0DJER1400100K,34Z1DJER2401200R,Consumption,`Load`,Production,Production_Thermal,Production_Hydro,Production_Wind,RO_RS,RS_HR,RS_BIH,HoliDays_HU,HoliDays_CG,HoliDays_RS,`NTC - ALRS`,`NTC - RSME`,Weather_forecast_t,`ConsumptionForecast - Plan D-1`,`Production - RDC`,datetime)
			SELECT 
            CONVERT(CONCAT(dt.datum, " ", LPAD(',y,', 2, "0"), ":00:00"), DATETIME) as date, 
            dt.datum,
			',i,',
			o.h',i,',
            SUM(DISTINCT CASE WHEN pe.`eic_code`= "34Z0DJER1400100K" THEN pe.H',x,' ELSE NULL END ) AS 34Z0DJER1400100K,
			SUM(DISTINCT CASE WHEN pe.`eic_code`= "34Z1DJER2401200R" THEN pe.H',x,' ELSE NULL END ) AS 34Z1DJER2401200R, 
			k.h',i,' AS Consumption,
			pp.h',i,' AS `Load`,
            pu.h',i,' AS Production,
			pt.h',i,' AS Production_Thermal,
			ph.h',i,' AS Production_Hydro,
            pw.h',i,' AS Production_Wind,
            SUM(DISTINCT CASE WHEN rpg.`smer`= "RO_RS" THEN rpg.v',i,' ELSE NULL END) AS RO_RS,
            SUM(DISTINCT CASE WHEN rpg.`smer`= "RS_HR" THEN rpg.v',i,' ELSE NULL END) AS RS_HR, 
            SUM(DISTINCT CASE WHEN rpg.`smer`= "RS_BIH" THEN rpg.v',i,' ELSE NULL END) AS RS_BIH,
            hd.HoliDays_HU AS Holidays_HU,
            hd.HoliDays_CG AS Holidays_CG,
            hd.HoliDays_RS AS Holidays_RS,
            SUM(DISTINCT CASE WHEN ntc.`smer`= "ALRS" THEN ntc.v',i,' ELSE NULL END ) AS NTC_ALRS,
            SUM(DISTINCT CASE WHEN ntc.`smer`= "RSME" THEN ntc.v',i,' ELSE NULL END ) AS NTC_RSME,
            mpt.v',i,',
            plan.h',i,' AS `ConsumptionForecast - Plan D-1`,
			rdc.h',i,' AS production_rdc,
            NOW()
						  FROM Datumi as dt
						JOIN meteo_prog_t as mpt ON dt.datum=mpt.datum
                         JOIN SCADA_RDC as rdc ON dt.datum=rdc.datum
						   JOIN obracunati_gubici AS o ON dt.datum=o.datum
						   JOIN konzum as k ON dt.datum=k.datum 
						   JOIN potrosnja as pp ON dt.datum=pp.datum 
                            JOIN  Proizvodnja_ukupno as pu ON dt.datum=pu.datum 
						      JOIN proizvodnja_TE as pt ON dt.datum=pt.datum   
						     JOIN proizvodnja_HE as ph ON dt.datum=ph.datum  
							LEFT JOIN proizvodnja_OI as pw ON dt.datum=pw.datum 
						     JOIN proizvodnja_entiteti as pe ON dt.datum=pe.datum 
						    JOIN HoliDays as hd ON dt.datum=hd.datum
						   JOIN ntc_po_granicama as ntc ON dt.datum=ntc.datum
                           JOIN `ConsumptionForecast - Plan D-1` AS plan ON dt.datum=plan.datum
						   JOIN razmena_po_granicama as rpg ON dt.datum=rpg.datum
						
			WHERE dt.datum = "',datum_start,'"');
             PREPARE stmnt FROM @sql; 
			 EXECUTE stmnt;        
			SET i=i+1;
	END WHILE;
   SET i=1;
   SET datum_start =  DATE_ADD(datum_start, INTERVAL 1 DAY);
END WHILE;
END