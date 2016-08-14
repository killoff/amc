
--
-- Table structure for table `protocol`
--

CREATE TABLE IF NOT EXISTS `protocol` (
  `protocol_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(500) NOT NULL,
  PRIMARY KEY (`protocol_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `protocol_rows`
--

CREATE TABLE IF NOT EXISTS `protocol_rows` (
  `row_id` int(10) unsigned NOT NULL,
  `parent_id` int(10) unsigned NOT NULL,
  `protocol_id` int(10) unsigned NOT NULL,
  `title` varchar(500) NOT NULL,
  `text` text NOT NULL,
  `action` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `protocol_rows`
--
ALTER TABLE `protocol_rows`
  ADD CONSTRAINT `protocol_rows_ibfk_1` FOREIGN KEY (`protocol_id`) REFERENCES `protocol` (`protocol_id`) ON DELETE CASCADE ON UPDATE CASCADE;
