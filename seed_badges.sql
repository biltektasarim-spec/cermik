-- Seed initial Badges for ROTAMIZ Gamification

INSERT INTO `badges` (`name`, `description`, `requirement_type`, `requirement_value`, `district_id`, `icon`) VALUES 
('Çermik Kaşifi', 'Çermik ilçesinde 5 farklı mekanda check-in yapın.', 'DISTRICT_SPECIFIC_CHECK_IN', 5, 3, 'assets/img/badges/cermik_explorer.png'),
('İlk Adım', 'Farklı bir mekanda ilk check-ininizi başarıyla tamamlayın.', 'CHECK_IN_COUNT', 1, NULL, 'assets/img/badges/first_step.png'),
('Gezgin', 'Toplamda 10 farklı mekanda check-in yaparak gezgin unvanı kazanın.', 'CHECK_IN_COUNT', 10, NULL, 'assets/img/badges/traveler.png'),
('Diyarbakır Kurdu', 'Şehir genelinde 25 check-in yaparak şehri avucunun içi gibi bilen biri olun!', 'CHECK_IN_COUNT', 25, NULL, 'assets/img/badges/pro_explorer.png');
