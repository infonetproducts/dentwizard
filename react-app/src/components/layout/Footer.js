import React from 'react';
import {
  Box,
  Container,
  Grid,
  Typography,
  Link,
  IconButton,
  Divider,
  Stack
} from '@mui/material';
import {
  Facebook,
  Instagram,
  LinkedIn,
  YouTube,
  Phone,
  LocationOn,
  Email
} from '@mui/icons-material';

const Footer = () => {
  const currentYear = new Date().getFullYear();

  return (
    <Box
      component="footer"
      sx={{
        bgcolor: '#32478a',
        color: 'white',
        pt: 6,
        pb: 3,
        mt: 'auto'
      }}
    >
      <Container maxWidth="lg">
        <Grid container spacing={4}>
          {/* Company Info */}
          <Grid item xs={12} md={4}>
            <Typography variant="h6" gutterBottom sx={{ fontWeight: 600 }}>
              Dent Wizard
            </Typography>
            <Stack spacing={1}>
              <Box sx={{ display: 'flex', alignItems: 'flex-start', gap: 1 }}>
                <LocationOn sx={{ fontSize: 20, mt: 0.5 }} />
                <Box>
                  <Typography variant="body2" sx={{ lineHeight: 1.6 }}>
                    Corporate Office Address<br />
                    4710 Earth City Expressway<br />
                    Bridgeton, MO 63044-3831
                  </Typography>
                </Box>
              </Box>
              <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                <Phone sx={{ fontSize: 20 }} />
                <Link
                  href="tel:800-336-8949"
                  color="inherit"
                  underline="hover"
                  sx={{ '&:hover': { color: '#3b82f6' } }}
                >
                  (800) DENT-WIZ
                </Link>
              </Box>
            </Stack>
          </Grid>

          {/* Social Media */}
          <Grid item xs={12} md={4}>
            <Typography variant="h6" gutterBottom sx={{ fontWeight: 600 }}>
              Follow Us
            </Typography>
            <Stack direction="row" spacing={1}>
              <IconButton
                component="a"
                href="https://www.facebook.com/DentWizardIntl"
                target="_blank"
                rel="noopener noreferrer"
                sx={{
                  color: 'white',
                  '&:hover': {
                    bgcolor: '#1877f2',
                    transform: 'translateY(-2px)',
                    transition: 'all 0.2s'
                  }
                }}
              >
                <Facebook />
              </IconButton>
              <IconButton
                component="a"
                href="https://www.instagram.com/dentwizardintl/"
                target="_blank"
                rel="noopener noreferrer"
                sx={{
                  color: 'white',
                  '&:hover': {
                    bgcolor: '#e4405f',
                    transform: 'translateY(-2px)',
                    transition: 'all 0.2s'
                  }
                }}
              >
                <Instagram />
              </IconButton>
              <IconButton
                component="a"
                href="https://www.linkedin.com/company/27768"
                target="_blank"
                rel="noopener noreferrer"
                sx={{
                  color: 'white',
                  '&:hover': {
                    bgcolor: '#0077b5',
                    transform: 'translateY(-2px)',
                    transition: 'all 0.2s'
                  }
                }}
              >
                <LinkedIn />
              </IconButton>
              <IconButton
                component="a"
                href="https://www.youtube.com/user/DentwizardIntl"
                target="_blank"
                rel="noopener noreferrer"
                sx={{
                  color: 'white',
                  '&:hover': {
                    bgcolor: '#ff0000',
                    transform: 'translateY(-2px)',
                    transition: 'all 0.2s'
                  }
                }}
              >
                <YouTube />
              </IconButton>
            </Stack>
            <Box sx={{ mt: 3 }}>
              <Typography variant="body2" sx={{ color: '#9ca3af', mb: 0.5 }}>
                Apparel Department
              </Typography>
              <Typography variant="body2" sx={{ fontWeight: 600, mb: 1 }}>
                Leader Graphics
              </Typography>
              <Link
                href="tel:814-528-5722"
                color="inherit"
                underline="hover"
                sx={{ 
                  display: 'block',
                  mb: 0.5,
                  '&:hover': { color: '#3b82f6' } 
                }}
              >
                (814) 528-5722
              </Link>
              <Link
                href="mailto:info@leadergraphic.com"
                color="inherit"
                underline="hover"
                sx={{ 
                  display: 'block',
                  '&:hover': { color: '#3b82f6' } 
                }}
              >
                info@leadergraphic.com
              </Link>
            </Box>
          </Grid>

          {/* Sales Policy */}
          <Grid item xs={12} md={4}>
            <Typography variant="h6" gutterBottom sx={{ fontWeight: 600 }}>
              Sales Policy
            </Typography>
            <Typography variant="body2" sx={{ lineHeight: 1.6, color: '#d1d5db' }}>
              Due to the nature of Custom Printed and Embroidered apparel and promotional items, 
              <strong> ALL SALES ARE FINAL</strong>. If you receive the wrong size item or misprinted 
              apparel, please contact us within 30 days to exchange at no cost.
            </Typography>
            <Typography variant="body2" sx={{ mt: 2, lineHeight: 1.6, color: '#d1d5db' }}>
              Please note that sizing can vary between manufacturers and styles. If you have any 
              questions on how a garment may fit or would like to purchase a sample product, please 
              email us or call our Apparel Department.
            </Typography>
            <Typography variant="body2" sx={{ mt: 2, fontStyle: 'italic', color: '#9ca3af' }}>
              *We are not able to reorder apparel due to the wrong size being purchased.
            </Typography>
          </Grid>
        </Grid>

        <Divider sx={{ my: 3, borderColor: '#374151' }} />

        {/* Copyright */}
        <Box sx={{ textAlign: 'center' }}>
          <Typography variant="body2" sx={{ color: '#9ca3af' }}>
            © {currentYear} Dent Wizard International. All rights reserved.
          </Typography>
        </Box>
      </Container>
    </Box>
  );
};

export default Footer;
